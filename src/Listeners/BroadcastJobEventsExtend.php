<?php

namespace CodedSultan\JobEngine\Listeners;

use CodedSultan\JobEngine\Events\JobCompleted;
use CodedSultan\JobEngine\Events\JobFailed;
use CodedSultan\JobEngine\Events\JobProgressed;
use App\Events\JobStatusUpdated;

class BroadcastJobEvents extends BaseJobListener
{
    public function handle(object $event): void
    {
        // Check if broadcasting is enabled for this job type
        if (! $this->shouldBroadcast($event->type)) {
            return;
        }

        broadcast(new JobStatusUpdated(
            jobId: $event->job->id,
            kind: $event->kind,
            type: $event->type,
            status: $event->job->status,
            processed: $event->job->processed,
            total: $event->job->total,
            userId: $event->userId
        ));
    }
}
