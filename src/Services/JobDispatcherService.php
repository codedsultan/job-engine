<?php

namespace CodedSultan\JobEngine\Services;

use Illuminate\Support\Facades\Bus;
use CodedSultan\JobEngine\Support\JobModelResolver;
use CodedSultan\JobEngine\Traits\TracksJobStatus;
use Illuminate\Database\Eloquent\Relations\Relation;

class JobDispatcherService
{
    use TracksJobStatus;

    public function __construct(protected JobRegistry $registry) {}

    public function dispatchJob(
        array $data,
        string $type,
        object $actor,
        ?int $chunkSize = null,
        ?string $strategy = null,
        bool $forceSingle = false,
        bool $allowDuplicates = false
    ): mixed {
        $meta = $this->registry->get($type);
        $kind = $meta['kind'];
        $modelClass = $meta['model'];
        $jobClass = $meta['job'];

        $total = count($data);
        $chunkSize = $chunkSize ?? config('job-engine.chunking.default_chunk_size', 100);
        $threshold = config('job-engine.chunking.chunk_threshold', 250);
        $strategy = $strategy ?? ($total > $threshold ? 'websocket' : 'polling');

        // Create job status record
        $this->beginJob($actor,  $type, $kind, $total, $strategy);
        // $jobStatus = $this->getJobStatus();
        // 👇 Get the ID of the last inserted job status
        $lastStatus = end($this->statusCache);
        $jobStatusId = $lastStatus?->getKey();

        $jobStatus = $this->getJobStatus($jobStatusId);


        if ($total === 0) {
            return $jobStatus;
        }

        $actorId = $actor->getKey();
        $actorType = array_search(get_class($actor), Relation::morphMap(), true);

        // Determine whether to chunk or not
        $shouldChunk = !$forceSingle && $total > $threshold;

        if (!$shouldChunk) {
            dispatch(new $jobClass(
                chunk: $data,
                modelClass: $modelClass,
                actorId: $actorId,
                actorType: $actorType,
                jobStatusId: $jobStatus->id,
                type: $type,
                kind: $kind
            ));

            return $jobStatus;
        }

        // Chunk and batch dispatch
        $chunks = array_chunk($data, $chunkSize);
        $jobs = collect($chunks)->map(fn ($chunk) => new $jobClass(
            chunk: $chunk,
            modelClass: $modelClass,
            actorId: $actorId,
            actorType: $actorType,
            jobStatusId: $jobStatus->id,
            type: $type,
            kind: $kind
        ));

        Bus::batch($jobs)->dispatch();

        return $jobStatus;
    }
}
