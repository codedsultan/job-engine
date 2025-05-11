<?php

namespace CodedSultan\JobEngine\Listeners;

use App\Events\JobCompleted;
use App\Models\User;
use App\Notifications\JobCompletedNotification;
use App\Services\JobRegistry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use CodedSultan\JobEngine\Support\BroadcastConfigHelper;


class NotifyJobOwner
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(object $event): void
    {
        $helper = app(BroadcastConfigHelper::class);

        if (! $helper->shouldNotify($event->type)) {
            return;
        }

        // $user = User::find($event->userId);
        // if (! $user) return;

        $actorClass = $event->actorType;
        $actor = $actorClass::find($event->actorId);

        if (! $actor || ! method_exists($actor, 'routeNotificationFor')) {
            return;
        }
        // Notification::route('mail', $user->email)
        //     ->notify(new JobCompletedNotification($event->job));

        if ($event->kind === 'import' && $event->type === 'user_import') {
            // Email notification, metrics, or activity log
        }
    }
}
