<?php

namespace CodedSultan\JobEngine\Jobs;

use Throwable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use CodedSultan\JobEngine\Support\JobModelResolver;
use CodedSultan\JobEngine\Support\RowIdentifierGenerator;
use CodedSultan\JobEngine\Services\{
    JobRegistry,
    JobStatusService,
    FailureAttemptHistoryService
};
use CodedSultan\JobEngine\Events\{
    JobCompleted,
    JobFailed
};
use CodedSultan\JobEngine\Traits\TracksJobStatus;
use CodedSultan\JobEngine\Support\ValidationRuleNormalizer;
use Illuminate\Support\Facades\Log;

class GenericImportChunkJob extends BaseChunkJob
{
    use TracksJobStatus;

    public function handle(): void
    {
        $registry = app(JobRegistry::class);
        $importerClass = $registry->getImporter($this->type);
        $jobStatusService = app(JobStatusService::class);

        if (! $importerClass || ! class_exists($importerClass)) {
            throw new \RuntimeException("No valid importer defined for type [{$this->type}]");
        }

        $importer = app($importerClass);
        // $rules = method_exists($importer, 'rules') ? $importer->rules() : [];
        $rawRules = method_exists($importer, 'rules') ? $importer->rules() : [];
        $rules = ValidationRuleNormalizer::normalize($rawRules, $this->meta['allow_duplicates'] ?? false);

        DB::transaction(function () use ($importer, $rules) {
            $failureModel = JobModelResolver::resolve($this->type, $this->kind, 'failure');

            foreach ($this->chunk as $row) {
                $rowIdentifier = $this->resolveRetryRowIdentifier($row);
                $isRetry = $this->meta['is_retry'] ?? false;
                $validator = Validator::make($row, $rules);

                if ($validator->fails()) {
                    $failure = $this->upsertFailure($failureModel, $rowIdentifier, $row, $validator->errors()->first());
                    $this->recordRetryHistory($failure, $validator->errors()->first(), $row);
                    // $this->incrementFailure($this->jobStatusId);
                    if (! $isRetry) {
                        $this->incrementFailure($this->jobStatusId); // Increments `failed` + `processed`
                    }
                    continue;
                }

                $data = method_exists($importer, 'transform') ? $importer->transform($row) : $row;
                // $data['job_status_id'] = $this->jobStatusId;
                try {
                    if (! empty($data)) {
                        $data['job_status_id'] = $this->jobStatusId;
                        $data['name'] = 'The Name';
                        Log::info($data);
                        $this->modelClass::create($data);


                    }
                } catch (Throwable $e) {
                    Log::error($e->getMessage());
                    $failure = $this->upsertFailure($failureModel, $rowIdentifier, $row, $e->getMessage());
                    $this->recordRetryHistory($failure, $e->getMessage(), $row);
                    // $this->incrementFailure($this->jobStatusId);
                    if (! $isRetry) {
                        $this->incrementFailure($this->jobStatusId);
                    }

                }

                try {
                    if ($failureId = $this->resolveFailureIdForRow($rowIdentifier)) {
                        $failure = $failureModel::find($failureId);
                        if ($failure) {
                            $failure->update(['resolved' => true, 'retrying' => false]);
                            $this->incrementRetrySuccess($this->jobStatusId, $this->type, $this->kind);
                            $this->decrementRetryFailure($this->jobStatusId, $this->type, $this->kind);
                            $this->recordRetryHistory($failure, 'Retry succeeded', $row, 'success');

                        }
                    }

                    // $this->incrementSuccess($this->jobStatusId);
                    if (! $isRetry) {
                        $this->incrementSuccess($this->jobStatusId); // Increments `successful` + `processed`
                    }
                }
                catch (Throwable $e) {
                    Log::error($e->getMessage());
                }
            }
        });

        $this->flushRetryTracking();

        $job = $this->resolveStatus($this->jobStatusId);
        $job = $jobStatusService->refreshStatus($job);

        match ($job->status) {
            'completed' => event(new JobCompleted($job, $this->kind, $this->type, $job->actor_id, $job->actor_type)),
            'failed'    => event(new JobFailed($job, $this->kind, $this->type, $job->actor_id, $job->actor_type, $job->message)),
            default     => null,
        };
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


    protected function resolveRetryRowIdentifier(array $row): string
    {
        $computed = RowIdentifierGenerator::from($row);

        if (($this->meta['allow_duplicates'] ?? false) || !isset($this->meta['row_identifiers'])) {
            return $computed;
        }

        foreach ($this->meta['row_identifiers'] as $failureId => $originalIdentifier) {
            $failureModel = JobModelResolver::resolve($this->type, $this->kind, 'failure');
            $failure = $failureModel::find($failureId);

            if ($failure && RowIdentifierGenerator::from($failure->payload) === $computed) {
                return $originalIdentifier;
            }
        }

        return $computed;
    }

}
