<?php

namespace CodedSultan\JobEngine\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use CodedSultan\JobEngine\Traits\TrackJobProgress;

abstract class BaseChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TrackJobProgress;

    public function __construct(
        public array $chunk,
        public string $modelClass,
        public int $adminId,
        public string $jobStatusId,
        public string $type,
        public string $kind
    ) {}

    abstract public function handle(): void;
}
