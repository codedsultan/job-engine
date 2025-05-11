<?php

namespace CodedSultan\JobEngine\Services;

use CodedSultan\JobEngine\Models\AbstractJobStatus;
use CodedSultan\JobEngine\Enums\JobStatusEnum;
use CodedSultan\JobEngine\Support\JobModelResolver;
use Illuminate\Database\Eloquent\Model;

class JobStatusService
{
    protected array $cache = [];
    /**
     * Resolve a JobStatus instance by ID.
     */
    public function resolve(string $jobStatusId,string $type, string $kind): AbstractJobStatus|Model
    {
        $model = JobModelResolver::resolve($type, $kind, 'status');

        if (! isset($this->cache[$jobStatusId])) {
            $this->cache[$jobStatusId] = $model::findOrFail($jobStatusId);
            //AbstractJobStatus::findOrFail($jobStatusId);
        }

        return $this->cache[$jobStatusId];

    }

    /**
     * Mark chunked job row success.
     */
    public function markSuccess(Model $job): void
    {
        if ($job->successful < $job->total) {
            $job->increment('successful');
        }
        if ($job->processed >= $job->total) {
            $job->increment('processed');
        }

    }

    /**
     * Mark chunked job row failure.
     */
    public function markFailure(Model $job): void
    {
        if ($job->failed < $job->total) {
            $job->increment('failed');
        }
        if ($job->processed < $job->total) {
            $job->increment('processed');
        }
    }

    /**
     * Mark atomic (single row) success.
     */
    public function atomicSuccess(Model $job): void
    {
        if ($job->successful >= $job->total) {
            return;
        }
        $job->increment('successful');
    }

    /**
     * Mark atomic (single row) failure.
     */
    public function atomicFailure(Model $job): void
    {
        if ($job->failed >= $job->total) {
            return;
        }
        $job->increment('failed');
    }

    /**
     * Retry success: increment success, decrement failed.
     */
    public function retrySuccess(Model $job): void
    {
        if ($job->successful < $job->total) {
            $job->increment('successful');
        }
        if ($job->failed > 0) {
            $job->decrement('failed');
        }
        // $job->decrement('failed');
    }

    /**
     * Retry failure: increment failed (don't touch processed).
     */
    public function retryFailure(Model $job): void
    {
        if ($job->failed < $job->total) {
            $job->increment('failed');
        }
    }

    /**
     * Recalculate and update job status based on unresolved failures.
     */
    // public function refreshStatus(Model $job): void
    // {
    //     $hasUnresolved = $job->failures()->where('resolved', false)->exists();

    //     $job->update([
    //         'status' => $hasUnresolved
    //             ? JobStatusEnum::Partial->value
    //             : JobStatusEnum::Completed->value,
    //     ]);
    // }
    public function refreshStatus(Model $job)
    {
        $hasUnresolved = $job->failures()->where('resolved', false)->exists();

        $finalStatus = match (true) {
            $job->successful === 0 && $hasUnresolved => JobStatusEnum::Failed->value,
            $hasUnresolved                          => JobStatusEnum::Partial->value,
            default                                 => JobStatusEnum::Completed->value,
        };

        $job->update(['status' => $finalStatus]);

        return $job->refresh();
    }

    /**
     * Mark job as failed and attach a message.
     */
    public function fail(Model $job, string $message = 'Job failed'): void
    {
        $job->update([
            'status' => JobStatusEnum::Failed->value,
            'message' => $message,
        ]);
    }

    /**
     * Mark job as completed and attach a message.
     */
    public function complete(Model $job, string $message = 'Job completed'): void
    {
        $job->update([
            'status' => JobStatusEnum::Completed->value,
            'message' => $message,
        ]);
    }
}
