<?php

namespace CodedSultan\JobEngine\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class JobStatusUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public string $jobId;
    public string $kind;
    public string $type;
    public string $status;
    public int $processed;
    public int $total;
    public int $userId;

    public function __construct(
        string $jobId,
        string $kind,
        string $type,
        string $status,
        int $processed,
        int $total,
        int $userId
    ) {
        $this->jobId = $jobId;
        $this->kind = $kind;
        $this->type = $type;
        $this->status = $status;
        $this->processed = $processed;
        $this->total = $total;
        $this->userId = $userId;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('job-status.' . $this->userId);
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

    public function broadcastAs(): string
    {
        return 'JobStatusUpdated';
    }
}
