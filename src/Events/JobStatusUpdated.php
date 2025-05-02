<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class JobStatusUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $jobId,
        public string $kind,
        public string $type,
        public string $status,
        public int $processed,
        public int $total,
        public int $userId
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("job-status.{$this->userId}");
    }

    public function broadcastAs(): string
    {
        return 'JobStatusUpdated'; // Or make this dynamic
    }

    public function broadcastWith(): array
    {
        return [
            'jobId' => $this->jobId,
            'kind' => $this->kind,
            'type' => $this->type,
            'status' => $this->status,
            'processed' => $this->processed,
            'total' => $this->total,
        ];
    }

    public function broadcastVia(): array
    {
        // 👇 Inject job type and resolve via helper
        $helper = app(\CodedSultan\JobEngine\Support\BroadcastConfigHelper::class);
        return [$helper->driver($this->type)];
    }
}
