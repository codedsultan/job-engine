<?php

namespace CodedSultan\JobEngine\Jobs;

use Illuminate\Support\Facades\DB;
use CodedSultan\JobEngine\Services\JobRegistry;
use CodedSultan\JobEngine\Support\JobModelResolver;

class GenericExportChunkJob extends BaseChunkJob
{
    public function handle(): void
    {
        try {
            $registry = app(JobRegistry::class);
            $exporterClass = $registry->getExporter($this->type);

            if (!$exporterClass || !class_exists($exporterClass)) {
                throw new \RuntimeException("No exporter found for type [{$this->type}]");
            }

            $exporter = app($exporterClass);

            DB::transaction(function () use ($exporter) {
                foreach ($this->chunk as $row) {
                    try {
                        $transformed = method_exists($exporter, 'transform')
                            ? $exporter->transform($row)
                            : $row;

                        if (method_exists($exporter, 'store')) {
                            $exporter->store($transformed);
                        }

                        $this->markChunkComplete($this->jobStatusId, $this->type, $this->kind);
                    } catch (\Throwable $e) {
                        $failureModel = JobModelResolver::resolve($this->type, $this->kind, 'failure');

                        $failureModel::create([
                            'job_status_id' => $this->jobStatusId,
                            'payload' => $row,
                            'message' => $e->getMessage(),
                            'row_identifier' => $row['id'] ?? null,
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            $this->markChunkFailed($this->jobStatusId, $e->getMessage(), $this->type, $this->kind);
            throw $e;
        }
    }
}
