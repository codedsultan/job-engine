<?php

namespace CodedSultan\JobEngine\Listeners;

use App\Events\JobProgressed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\JobStatusUpdated;
class BroadcastJobEvents
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
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
