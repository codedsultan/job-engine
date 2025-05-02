<?php

namespace App\Listeners\JobEngine;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\JobCompletedNotification;
use CodedSultan\JobEngine\Support\BroadcastConfigHelper;

class NotifyJobOwner
{
    public function handle(object $event): void
    {
        $helper = app(BroadcastConfigHelper::class);

        if (! $helper->shouldNotify($event->type)) {
            return;
        }

        $user = User::find($event->userId);
        if (! $user) return;

        Notification::route('mail', $user->email)
            ->notify(new JobCompletedNotification($event->job));

        // Optional: add custom logging or notification logic here
    }
}
