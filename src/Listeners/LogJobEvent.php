<?php

namespace CodedSultan\JobEngine\Listeners;

use App\Events\JobFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogJobEvent
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        Log::info("[{$event->kind}] Job {$event->type} {$event->job->id} updated", [
            'status' => $event->job->status,
            'user_id' => $event->userId,
        ]);
    }
}
