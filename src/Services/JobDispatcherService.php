<?php

namespace CodedSultan\JobEngine\Services;

use Illuminate\Support\Facades\Bus;
use CodedSultan\JobEngine\Support\JobModelResolver;
use CodedSultan\JobEngine\Traits\TracksJobStatus;

class JobDispatcherService
{
    use TracksJobStatus;

    public function __construct(protected JobRegistry $registry) {}

    public function dispatchJob(
        array $data,
        string $type,
        int $adminId,
        ?int $chunkSize = null,
        ?string $strategy = null,
        bool $forceSingle = false
    ): mixed {
        $meta = $this->registry->get($type);
        $kind = $meta['kind'];
        $modelClass = $meta['model'];
        $jobClass = $meta['job'];

        $total = count($data);
        $chunkSize = $chunkSize ?? config('jobs.chunking.default_chunk_size', 100);
        $threshold = config('jobs.chunking.chunk_threshold', 250);
        $strategy = $strategy ?? ($total > $threshold ? 'websocket' : 'polling');

        // Create job status record
        $this->beginJob($adminId, $type, $kind, $total, $strategy);
        $jobStatus = $this->getJobStatus();

        if ($total === 0) {
            return $jobStatus;
        }

        // Determine whether to chunk or not
        $shouldChunk = !$forceSingle && $total > $threshold;

        if (!$shouldChunk) {
            dispatch(new $jobClass(
                chunk: $data,
                modelClass: $modelClass,
                adminId: $adminId,
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
            adminId: $adminId,
            jobStatusId: $jobStatus->id,
            type: $type,
            kind: $kind
        ));

        Bus::batch($jobs)->dispatch();

        return $jobStatus;
    }
}
