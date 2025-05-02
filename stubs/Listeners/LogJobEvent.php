<?php

namespace App\Listeners\JobEngine;

use Illuminate\Support\Facades\Log;
use CodedSultan\JobEngine\Support\BroadcastConfigHelper;

class LogJobEvent
{
    public function handle(object $event): void
    {
        $helper = app(BroadcastConfigHelper::class);

        if (! $helper->shouldLog($event->type)) {
            return;
        }

        Log::info("[{$event->kind}] Job {$event->type} {$event->job->id} updated", [
            'status' => $event->job->status,
            'user_id' => $event->userId,
        ]);
    }
}
