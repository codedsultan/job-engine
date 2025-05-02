<?php

namespace App\Listeners\JobEngine;

use App\Events\JobStatusUpdated;
use CodedSultan\JobEngine\Support\BroadcastConfigHelper;

class BroadcastJobEvents
{
    public function handle(object $event): void
    {
        $helper = app(BroadcastConfigHelper::class);

        if (! $helper->enabled($event->type)) {
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
