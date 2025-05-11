<?php

namespace CodedSultan\JobEngine\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use CodedSultan\JobEngine\Services\JobRegistry;
use CodedSultan\JobEngine\Support\JobModelResolver;
use Illuminate\Support\Str;

class GenericImportChunkJob extends BaseChunkJob
{
    public function handle(): void
    {
        try {
            $registry = app(JobRegistry::class);
            $importerClass = $registry->getImporter($this->type);

            if (!$importerClass || !class_exists($importerClass)) {
                throw new \RuntimeException("No valid importer defined for type [{$this->type}]");
            }

            $importer = app($importerClass);
            $rules = method_exists($importer, 'rules') ? $importer->rules() : [];

            // DB::transaction(function () use ($importer, $rules) {
            //     foreach ($this->chunk as $row) {
            //         $validator = Validator::make($row, $rules);

            //         if ($validator->fails()) {
            //             $failureModel = JobModelResolver::resolve($this->type, $this->kind, 'failure');
            //             $failureModel::create([
            //                 'job_status_id' => $this->jobStatusId,
            //                 'payload' => $row,
            //                 'message' => $validator->errors()->first(),
            //                 'row_identifier' => $row['email'] ?? null,
            //             ]);
            //             continue;
            //         }

            //         $data = method_exists($importer, 'transform')
            //             ? $importer->transform($row)
            //             : $row;

            //         $this->modelClass::create($data);
            //         $this->markChunkComplete($this->jobStatusId, $this->type, $this->kind);
            //     }
            // });
            DB::transaction(function () use ($importer, $rules) {
                $failureModel = JobModelResolver::resolve($this->type, $this->kind, 'failure');

                foreach ($this->chunk as $row) {
                    $validator = Validator::make($row, $rules);

                    if ($validator->fails()) {
                        $failureModel::create([
                            'id' => Str::ulid(),
                            'job_status_id' => $this->jobStatusId,
                            'payload' => $row,
                            'message' => $validator->errors()->first(),
                            'row_identifier' => $row['email'] ?? null,
                            'actor_id' => $this->actorId ?? null,
                            'actor_type' => $this->actorType ?? null,
                        ]);

                        $this->markChunkFailed($this->jobStatusId, 'Validation failed', $this->type, $this->kind);
                        continue;
                    }

                    $data = method_exists($importer, 'transform')
                        ? $importer->transform($row)
                        : $row;

                    try {
                        if (!empty($data)) {
                            $this->modelClass::create($data);
                            // ✅ If retrying, mark the failure as resolved
                            if (isset($this->meta['failure_id'])) {
                                $failureModel::where('id', $this->meta['failure_id'])->update([
                                    'resolved' => true,
                                    'retrying' => false,
                                ]);

                                $this->incrementRetrySuccess($this->jobStatusId, $this->type, $this->kind);
                                $this->decrementRetryFailure($this->jobStatusId, $this->type, $this->kind);


                            }


                            $this->markChunkComplete($this->jobStatusId, $this->type, $this->kind);
                        }
                    } catch (\Throwable $e) {
                        $failureModel::create([
                            'job_status_id' => $this->jobStatusId,
                            'payload' => $row,
                            'message' => $e->getMessage(),
                            'row_identifier' => $row['email'] ?? null,
                            'actor_id' => $this->actorId ?? null,
                            'actor_type' => $this->actorType ?? null,
                        ]);

                        $this->markChunkFailed($this->jobStatusId, $e->getMessage(), $this->type, $this->kind);
                    }
                }
            });

        } catch (\Throwable $e) {
            $this->markChunkFailed($this->jobStatusId, $e->getMessage(), $this->type, $this->kind);
            throw $e;
        }
    }
}
