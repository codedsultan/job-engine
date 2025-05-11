<?php

namespace CodedSultan\JobEngine\Utils;

use CodedSultan\JobEngine\Support\JobModelResolver;

class RetryTracker
{
    protected array $successCounts = [];
    protected array $failureDecrements = [];

    public function recordSuccess(string $jobStatusId, string $type, string $kind): void
    {
        $key = "{$type}:{$kind}:{$jobStatusId}";
        $this->successCounts[$key] = ($this->successCounts[$key] ?? 0) + 1;
    }

    public function recordFailureResolved(string $jobStatusId, string $type, string $kind): void
    {
        $key = "{$type}:{$kind}:{$jobStatusId}";
        $this->failureDecrements[$key] = ($this->failureDecrements[$key] ?? 0) + 1;
    }

    public function flush(): void
    {
        foreach ($this->successCounts as $key => $success) {
            [$type, $kind, $jobStatusId] = explode(':', $key);
            $model = JobModelResolver::resolve($type, $kind, 'status');
            $model::where('id', $jobStatusId)->increment('successful', $success);
        }

        foreach ($this->failureDecrements as $key => $decr) {
            [$type, $kind, $jobStatusId] = explode(':', $key);
            $model = JobModelResolver::resolve($type, $kind, 'status');
            $model::where('id', $jobStatusId)->decrement('failed', $decr);
        }
    }
}
