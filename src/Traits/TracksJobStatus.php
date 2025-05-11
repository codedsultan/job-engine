<?php

namespace CodedSultan\JobEngine\Traits;

use CodedSultan\JobEngine\Services\JobStatusService;
use CodedSultan\JobEngine\Support\JobModelResolver;
use Illuminate\Database\Eloquent\Relations\Relation;
use CodedSultan\JobEngine\Models\AbstractJobStatus;
use Illuminate\Database\Eloquent\Model;
trait TracksJobStatus
{
    protected array $statusCache = [];

    protected function jobStatusService(): JobStatusService
    {
        return app(JobStatusService::class);
    }

    public function beginJob(
        object $actor,
        string $type,
        string $kind = 'import',
        int $total = 0,
        string $strategy = 'polling'
    ): void {
        $model = JobModelResolver::resolve($type, $kind, 'status');

        /** @var AbstractJobStatus $status */
        $status = $model::create([
            'actor_id'   => $actor->getKey(),
            'actor_type' => array_search(get_class($actor), Relation::morphMap(), true),
            'kind'       => $kind,
            'type'       => $type,
            'total'      => $total,
            'processed'  => 0,
            'status'     => 'processing',
            'strategy'   => $strategy,
        ]);

        $this->statusCache[$status->getKey()] = $status;
    }

    protected function resolveStatus(string $jobStatusId): AbstractJobStatus|Model

    {
        if (!isset($this->statusCache[$jobStatusId])) {
            $this->statusCache[$jobStatusId] = $this->jobStatusService()->resolve($jobStatusId,$this->type,$this->kind);
        }

        return $this->statusCache[$jobStatusId];
    }

    public function incrementSuccess(string $jobStatusId): void
    {
        $this->jobStatusService()->markSuccess($this->resolveStatus($jobStatusId));
    }

    public function incrementFailure(string $jobStatusId): void
    {
        $this->jobStatusService()->markFailure($this->resolveStatus($jobStatusId));
    }

    public function decrementFailure(string $jobStatusId): void
    {
        $this->resolveStatus($jobStatusId)->decrement('failed');
    }

    public function completeJob(string $jobStatusId, string $message = 'Job completed'): void
    {
        $this->jobStatusService()->complete($this->resolveStatus($jobStatusId), $message);
    }

    public function failJob(string $jobStatusId, string $message = 'Job failed'): void
    {
        $this->jobStatusService()->fail($this->resolveStatus($jobStatusId), $message);
    }

    public function getJobStatus(string $jobStatusId):  AbstractJobStatus|Model
    {
        return $this->resolveStatus($jobStatusId);
    }
}
