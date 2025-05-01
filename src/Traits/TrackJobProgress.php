<?php

namespace CodedSultan\JobEngine\Traits;

use CodedSultan\JobEngine\Support\JobModelResolver;
use CodedSultan\JobEngine\Events\{
    JobCompleted,
    JobFailed,
    JobProgressed
};

trait TrackJobProgress
{
    public function markChunkComplete(string $jobStatusId, string $type, string $kind): void
    {
        $model = JobModelResolver::resolve($type, $kind, 'status');
        $job = $model::where('id', $jobStatusId)->firstOrFail();

        $job->increment('processed');

        event(new JobProgressed($job, $kind, $type, $job->user_id));

        if ($job->processed >= $job->total && $job->status === 'processing') {
            $job->update(['status' => 'completed']);
            event(new JobCompleted($job, $kind, $type, $job->user_id));
        }
    }

    public function markChunkFailed(string $jobStatusId, string $message, string $type, string $kind): void
    {
        $model = JobModelResolver::resolve($type, $kind, 'status');
        $job = $model::where('id', $jobStatusId)->firstOrFail();

        $job->update(['status' => 'failed', 'message' => $message]);

        event(new JobFailed($job, $kind, $type, $job->user_id, $message));
    }
}
