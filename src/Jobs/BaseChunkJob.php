<?php

namespace CodedSultan\JobEngine\Jobs;

use CodedSultan\JobEngine\Support\JobModelResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use CodedSultan\JobEngine\Traits\TrackJobProgress;
use CodedSultan\JobEngine\Traits\TracksRetryStats;


abstract class BaseChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TrackJobProgress, TracksRetryStats;

    public array $meta = [];

    public function __construct(
        public array $chunk,
        public string $modelClass,
        public int|string $actorId,
        public string $actorType,
        public string $jobStatusId,
        public string $type,
        public string $kind,
        public bool $allowDuplicates = false
    ) {}

    abstract public function handle(): void;

    public function withMeta(array $meta): static
    {
        $this->meta = $meta;
        return $this;
    }

    // protected function resolveFailureIdForRow(array $row): ?string
    // {
    //     if (! isset($this->meta['failure_ids'])) {
    //         return null;
    //     }

    //     // Generate a fallback row identifier using a consistent hashing strategy
    //     // $identifier = $row['row_identifier']
    //     //     ?? $row['email']
    //     //     ?? $row['id']
    //     //     ?? $row['username']
    //     //     ?? md5(json_encode($row));
    //     $identifier = md5(json_encode($row));
    //     // Use a static map to cache lookups for this batch
    //     static $failureMap = [];

    //     if (empty($failureMap)) {
    //         $failureModel = \CodedSultan\JobEngine\Support\JobModelResolver::resolve($this->type, $this->kind, 'failure');

    //         $failures = $failureModel::whereIn('id', $this->meta['failure_ids'])->get();
    //         foreach ($failures as $failure) {
    //             if (! empty($failure->row_identifier)) {
    //                 $failureMap[$failure->row_identifier] = $failure->id;
    //             }
    //         }
    //     }

    //     return $failureMap[$identifier] ?? null;
    // }

    protected function resolveFailureIdForRow(string $rowIdentifier): ?string
    {
        static $failureMap = [];
        $failureId = isset($this->meta['failure_ids']) ? $this->meta['failure_ids'] : [];

        if (empty($failureMap) && count($failureId) > 0) {
            $failureModel = JobModelResolver::resolve($this->type, $this->kind, 'failure');
            $failures = $failureModel::whereIn('id', $this->meta['failure_ids'])->get();

            foreach ($failures as $failure) {
                if (!empty($failure->row_identifier)) {
                    $failureMap[$failure->row_identifier] = $failure->id;
                }
            }
        }

        return $failureMap[$rowIdentifier] ?? null;
    }


}
