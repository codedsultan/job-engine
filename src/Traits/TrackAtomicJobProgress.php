<?php

namespace CodedSultan\JobEngine\Traits;

use CodedSultan\JobEngine\Services\JobStatusService;
use CodedSultan\JobEngine\Events\JobProgressed;
use CodedSultan\JobEngine\Models\AbstractJobStatus;

trait TrackAtomicJobProgress
{
    protected function jobStatusService(): JobStatusService
    {
        return app(JobStatusService::class);
    }

    public function markSuccess(string $jobStatusId, string $type, string $kind): void
    {
        /** @var AbstractJobStatus $job */
        $job = $this->jobStatusService()->resolve($jobStatusId,$type, $kind);
        $this->jobStatusService()->atomicSuccess($job);

        event(new JobProgressed($job, $kind, $type, $job->actor_id, $job->actor_type));
    }

    public function markFailure(string $jobStatusId, string $message, string $type, string $kind): void
    {
        /** @var AbstractJobStatus $job */
        $job = $this->jobStatusService()->resolve($jobStatusId,$type, $kind);
        $this->jobStatusService()->atomicFailure($job);

        event(new JobProgressed($job, $kind, $type, $job->actor_id, $job->actor_type));
    }
}
