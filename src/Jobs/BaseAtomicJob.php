<?php

namespace CodedSultan\JobEngine\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use CodedSultan\JobEngine\Traits\TrackAtomicJobProgress;
use CodedSultan\JobEngine\Traits\TracksRetryStats;

abstract class BaseAtomicJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TrackAtomicJobProgress , TracksRetryStats;


    public array $meta = [];

    public function __construct(
        public array $row,
        public string $modelClass,
        public int|string $actorId,
        public string $actorType,
        public string $jobStatusId,
        public string $type,
        public string $kind
    ) {}

    abstract public function handle(): void;

    public function withMeta(array $meta): static
    {
        $this->meta = $meta;
        return $this;
    }
}
