<?php

namespace CodedSultan\JobEngine\Listeners;

use App\Events\JobStatusUpdated;
use CodedSultan\JobEngine\Support\BroadcastConfigHelper;
use Illuminate\Support\Facades\Log;

class BroadcastJobEvents
{
    public function handle(object $event): void
    {
        $helper = app(BroadcastConfigHelper::class);

        // Optional: Log broadcast attempts
        Log::debug('[BroadcastJobEvents] Handling job update', [
            'job_id' => $event->job->id ?? null,
            'type' => $event->type ?? null,
            'actorId' => $event->actorId ?? null,
            'actorType' => $event->actorType ?? null,
        ]);

        if (! $helper->enabled($event->type)) {
            Log::info("[BroadcastJobEvents] Broadcasting disabled for type: {$event->type}");
            return;
        }


        // Prevent missing required fields
        if (!isset($event->job, $event->type, $event->actorId, $event->actorType)) {
            Log::warning('[BroadcastJobEvents] Missing required job/event fields', [
                'event' => get_class($event),
            ]);
            return;
        }


        // Fire broadcast
        broadcast(new JobStatusUpdated(
            jobId: $event->job->id,
            kind: $event->kind,
            type: $event->type,
            status: $event->job->status,
            processed: $event->job->processed,
            successful: $event->job->successful ?? 0, // ✅ new
            failed: $event->job->failed ?? 0,         // ✅ new
            total: $event->job->total,
            actorId: $event->actorId,
            actorType: $event->actorType,
        ))->toOthers(); // 👈 Optional: avoid self-broadcasts in multi-tab

    }
}
