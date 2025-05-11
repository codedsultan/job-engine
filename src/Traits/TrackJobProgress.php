<?php

namespace CodedSultan\JobEngine\Traits;

use CodedSultan\JobEngine\Support\JobModelResolver;
use CodedSultan\JobEngine\Events\{
    JobCompleted,
    JobFailed,
    JobProgressed
};
use CodedSultan\JobEngine\Services\JobStatusService;

trait TrackJobProgress
{
    protected function jobStatusService(): JobStatusService
    {
        return app(JobStatusService::class);
    }

    public function markChunkComplete(string $jobStatusId, string $type, string $kind): void
    {
        $model = JobModelResolver::resolve($type, $kind, 'status');
        $job = $model::where('id', $jobStatusId)->firstOrFail();

        $job->increment('processed');
        $job->increment('successful');

        event(new JobProgressed($job, $kind, $type, $job->actor_id, $job->actor_type));

        if ($job->processed >= $job->total && $job->status === 'processing') {
            $this->jobStatusService()->refreshStatus($job);
            event(new JobCompleted($job, $kind, $type, $job->actor_id, $job->actor_type));
        }
    }

    public function markChunkFailed(string $jobStatusId, string $message, string $type, string $kind): void
    {
        $model = JobModelResolver::resolve($type, $kind, 'status');
        $job = $model::where('id', $jobStatusId)->firstOrFail();

        $job->increment('processed');
        $job->increment('failed');

        event(new JobProgressed($job, $kind, $type, $job->actor_id, $job->actor_type));

        if ($job->processed >= $job->total && $job->status === 'processing') {
            $this->jobStatusService()->refreshStatus($job);
            event(new JobFailed($job, $kind, $type, $job->actor_id, $job->actor_type, $message));
        }
    }
}
