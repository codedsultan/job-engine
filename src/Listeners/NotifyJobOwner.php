<?php

namespace CodedSultan\JobEngine\Listeners;

use App\Events\JobCompleted;
use App\Models\User;
use App\Notifications\JobCompletedNotification;
use App\Services\JobRegistry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class NotifyJobOwner
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

        $user = User::find($event->userId);

        if (!$user) return;

        Notification::route('mail', $user->email)
            ->notify(new JobCompletedNotification($event->job));
        if ($event->kind === 'import' && $event->type === 'user_import') {
            // Email notification, metrics, or activity log
        }

        if ($event->kind === 'sync') {
            // Trigger downstream processing
        }

        $registry = app(JobRegistry::class);
        if ($registry->shouldBroadcast($event->type)) {
            // Fire WebSocket broadcast
          }

    }
}
