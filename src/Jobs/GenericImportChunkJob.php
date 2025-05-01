<?php

namespace CodedSultan\JobEngine\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use CodedSultan\JobEngine\Services\JobRegistry;
use CodedSultan\JobEngine\Support\JobModelResolver;

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

            DB::transaction(function () use ($importer, $rules) {
                foreach ($this->chunk as $row) {
                    $validator = Validator::make($row, $rules);

                    if ($validator->fails()) {
                        $failureModel = JobModelResolver::resolve($this->type, $this->kind, 'failure');
                        $failureModel::create([
                            'job_status_id' => $this->jobStatusId,
                            'payload' => $row,
                            'message' => $validator->errors()->first(),
                            'row_identifier' => $row['email'] ?? null,
                        ]);
                        continue;
                    }

                    $data = method_exists($importer, 'transform')
                        ? $importer->transform($row)
                        : $row;

                    $this->modelClass::create($data);
                    $this->markChunkComplete($this->jobStatusId, $this->type, $this->kind);
                }
            });
        } catch (\Throwable $e) {
            $this->markChunkFailed($this->jobStatusId, $e->getMessage(), $this->type, $this->kind);
            throw $e;
        }
    }
}
