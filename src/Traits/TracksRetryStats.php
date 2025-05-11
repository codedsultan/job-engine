<?php

namespace CodedSultan\JobEngine\Traits;

use CodedSultan\JobEngine\Support\JobModelResolver;

trait TracksRetryStats
{
    protected array $retryIncrements = [];

    protected function bufferIncrement(string $jobStatusId, string $type, string $kind, string $column): void
    {
        $this->retryIncrements[$jobStatusId] ??= [
            'type' => $type,
            'kind' => $kind,
            'successful' => 0,
            'failed' => 0,
        ];
        $this->retryIncrements[$jobStatusId][$column]++;
    }

    public function incrementRetrySuccess(string $jobStatusId, string $type, string $kind): void
    {
        $this->bufferIncrement($jobStatusId, $type, $kind, 'successful');
    }

    public function decrementRetryFailure(string $jobStatusId, string $type, string $kind): void
    {
        $this->bufferIncrement($jobStatusId, $type, $kind, 'failed');
    }

    public function flushRetryTracking(): void
    {
        foreach ($this->retryIncrements as $jobStatusId => $data) {
            $model = JobModelResolver::resolve($data['type'], $data['kind'], 'status');
            $job = $model::where('id', $jobStatusId)->first();

            if ($job) {
                if ($data['successful'] > 0) {
                    $job->increment('successful', $data['successful']);
                }
                if ($data['failed'] > 0) {
                    $job->decrement('failed', $data['failed']);
                }
            }
        }

        $this->retryIncrements = [];
    }

    public function __destruct()
    {
        // Auto-flush only if used in an atomic job
        if ($this instanceof \CodedSultan\JobEngine\Jobs\BaseAtomicJob) {
            $this->flushRetryTracking();
        }
    }
}
