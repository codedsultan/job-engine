<?php

namespace CodedSultan\JobEngine\Traits;

use CodedSultan\JobEngine\Support\JobModelResolver;

trait TracksJobStatus
{
    protected object $status;

    public function beginJob(int $userId, string $type, string $kind = 'import', int $total = 0, string $strategy = 'polling'): void
    {
        $model = JobModelResolver::resolve($type, $kind, 'status');

        $this->status = $model::create([
            'user_id' => $userId,
            'kind' => $kind,
            'type' => $type,
            'total' => $total,
            'processed' => 0,
            'status' => 'processing',
            'strategy' => $strategy,
        ]);
    }

    public function incrementProcessed(int $count = 1): void
    {
        $this->status?->increment('processed', $count);
    }

    public function failJob(string $message = 'Job failed'): void
    {
        $this->status?->update(['status' => 'failed', 'message' => $message]);
    }

    public function completeJob(string $message = 'Job completed'): void
    {
        $this->status?->update(['status' => 'completed', 'message' => $message]);
    }

    public function getJobStatus(): ?object
    {
        return $this->status ?? null;
    }
}
