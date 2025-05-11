<?php

namespace CodedSultan\JobEngine\Jobs;

use Throwable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use CodedSultan\JobEngine\Support\JobModelResolver;
use CodedSultan\JobEngine\Support\RowIdentifierGenerator;
use CodedSultan\JobEngine\Services\{
    JobRegistry,
    FailureAttemptHistoryService
};
use CodedSultan\JobEngine\Traits\TracksRetryStats;
use CodedSultan\JobEngine\Traits\TrackAtomicJobProgress;
use CodedSultan\JobEngine\Support\ValidationRuleNormalizer;
use Illuminate\Support\Facades\Log;

class GenericAtomicImportJob extends BaseAtomicJob
{
    use TrackAtomicJobProgress, TracksRetryStats;

    public function handle(): void
    {
        $registry = app(JobRegistry::class);
        $importerClass = $registry->getImporter($this->type);

        if (! $importerClass || ! class_exists($importerClass)) {
            throw new \RuntimeException("No valid importer defined for type [{$this->type}]");
        }

        $importer = app($importerClass);
        $rawRules = method_exists($importer, 'rules') ? $importer->rules() : [];
        $rules = ValidationRuleNormalizer::normalize($rawRules, $this->meta['allow_duplicates'] ?? false);
        $isRetry = $this->meta['is_retry'] ?? false;

        // $rules = method_exists($importer, 'rules') ? $importer->rules() : [];
        // $allowDuplicates = $this->meta['allow_duplicates'] ?? false;

        // // Dynamically remove unique constraints if allow_duplicates is true
        // if ($allowDuplicates) {
        //     foreach ($rules as $field => &$fieldRules) {
        //         $fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : (array) $fieldRules;
        //         $fieldRules = array_filter($fieldRules, fn ($rule) => ! (is_string($rule) && str_starts_with($rule, 'unique:')));
        //     }
        //     unset($fieldRules); // break reference
        // }

        Log::info('inside generic import job');
        DB::transaction(function () use ($importer, $rules , $isRetry) {
            $failureModel = JobModelResolver::resolve($this->type, $this->kind, 'failure');
            $rowIdentifier = $this->resolveRetryRowIdentifier($this->row);
            $statusModel = JobModelResolver::resolve($this->type, $this->kind, 'status');
            $validator = Validator::make($this->row, $rules);

            if ($validator->fails()) {
                $failure = $this->upsertFailure($failureModel, $rowIdentifier, $this->row, $validator->errors()->first());
                $this->recordRetryHistory($failure, $validator->errors()->first(), $this->row);
                if (! $isRetry) {
                    $this->markFailure($this->jobStatusId, 'Validation failed', $this->type, $this->kind);
                }
                return;
            }

            $data = method_exists($importer, 'transform') ? $importer->transform($this->row) : $this->row;

            try {
                if (! empty($data)) {
                    $data['job_status_id'] = $this->jobStatusId;
                    $data['name'] = 'The Name';

                    $failedCount = $statusModel::where('job_status_id', $this->jobStatusId)->first()->failed;
                    $successfulCount = $statusModel::where('job_status_id', $this->jobStatusId)->first()->successful;
                    // $processedCount = $statusModel::where('job_status_id', $this->jobStatusId)->first()->processed;
                    $totalCount = $statusModel::where('job_status_id', $this->jobStatusId)->first()->total;
                    $failedCount = $failedCount > 0 ? $failedCount - 1 : $failedCount;
                    $successfulCount = $successfulCount >= $totalCount ? $successfulCount : $successfulCount + 1;

                    Log::info($data);

                    $this->modelClass::create($data);

                    if (isset($this->meta['failure_id'])) {
                        $failureModel::where('id', $this->meta['failure_id'])->update([
                            'resolved' => true,
                            'retrying' => false,
                            'message' => 'Retry succeeded',
                        ]);
                        $statusModel::where('job_status_id', $this->jobStatusId)->update([
                            'failed' => $failedCount,
                            'successful' => $successfulCount,
                        ]);
                        $this->incrementRetrySuccess($this->jobStatusId, $this->type, $this->kind);
                        $this->decrementRetryFailure($this->jobStatusId, $this->type, $this->kind);

                        $this->recordRetryHistory($failureModel::where('id', $this->meta['failure_id'])->first(), 'Retry succeeded', $this->row, 'success');
                    }
                    if (! $isRetry) {
                        $this->markSuccess($this->jobStatusId, $this->type, $this->kind);
                    }
                }
            } catch (Throwable $e) {
                $failure = $this->upsertFailure($failureModel, $rowIdentifier, $this->row, $e->getMessage());
                $this->recordRetryHistory($failure, $e->getMessage(), $this->row);
                if (! $isRetry) {
                    $this->markFailure($this->jobStatusId, 'Storage failed', $this->type, $this->kind);
                }
            }
        });
    }

    // protected function resolveRetryRowIdentifier(array $row): string
    // {
    //     $computed = RowIdentifierGenerator::from($row);

    //     if (!isset($this->meta['row_identifiers'])) {
    //         return $computed;
    //     }

    //     foreach ($this->meta['row_identifiers'] as $failureId => $originalIdentifier) {
    //         $failureModel = JobModelResolver::resolve($this->type, $this->kind, 'failure');
    //         $failure = $failureModel::find($failureId);

    //         if ($failure && RowIdentifierGenerator::from($failure->payload) === $computed) {
    //             return $originalIdentifier;
    //         }
    //     }

    //     return $computed;
    // }

    protected function resolveRetryRowIdentifier(array $row): string
    {
        $computed = RowIdentifierGenerator::from($row);

        if (($this->meta['allow_duplicates'] ?? false) || !isset($this->meta['row_identifiers'])) {
            return $computed;
        }

        return $this->meta['row_identifier']
            ?? RowIdentifierGenerator::from($row);

    }


    protected function upsertFailure($failureModel, string $rowIdentifier, array $row, string $message)
    {
        return $failureModel::updateOrCreate(
            [
                'job_status_id' => $this->jobStatusId,
                'row_identifier' => $rowIdentifier,
            ],
            [
                'payload' => $row,
                'message' => $message,
                'actor_id' => $this->actorId ?? null,
                'actor_type' => $this->actorType ?? null,
                'retrying' => false,
            ]
        );
    }

    // protected function recordRetryHistory($failure, string $message, array $row)
    // {
    //     app(FailureAttemptHistoryService::class)->recordAttempt($failure, [
    //         'message' => $message,
    //         'payload' => $row,
    //         'attempted_at' => now()->toDateTimeString(),
    //     ]);
    // }
    protected function recordRetryHistory($failure, string $message, array $row, string $status = 'failed')
    {
        app(FailureAttemptHistoryService::class)->recordAttempt($failure, [
            'message' => $message,
            'payload' => $row,
            'attempted_at' => now()->toDateTimeString(),
            'status' => $status,
        ]);
    }

}
