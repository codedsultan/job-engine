<?php

namespace CodedSultan\JobEngine\Events;

use App\Models\JobStatus;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;



    /**
     * Create a new event instance.
     */
    public function __construct(
        public mixed $job,
        public string $kind,
        public string $type,
        // public string|int $userId,
        public int|string $actorId,
        public string $actorType,
        public ?string $message = null
    ) {}

    // {
    //     $this->job = $job;
    //     $this->id = $job->id;
    //     $this->kind = $job->kind ?? 'unknown';
    //     $this->type = $job->type ?? 'unknown';
    //     $this->userId = $job->user_id ?? null;
    // }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
