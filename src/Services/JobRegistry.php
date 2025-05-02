<?php

namespace CodedSultan\JobEngine\Services;

use Illuminate\Support\Arr;

class JobRegistry
{
    /**
     * Flattened job type map.
     * e.g. ['user_import' => [..., 'kind' => 'import']]
     */
    protected array $flat;

    public function __construct()
    {
        $this->flat = $this->flatten(config('job-engine.types', []));
    }

    /**
     * Get all registered job types, flattened.
     */
    public function all(): array
    {
        return $this->flat;
    }

    /**
     * Get a job type by name (e.g. 'user_import').
     */
    public function get(string $type): array
    {
        if (!isset($this->flat[$type])) {
            throw new \InvalidArgumentException("Job type [$type] is not registered.");
        }

        return $this->flat[$type];
    }

    /**
     * Return a specific property of a job type.
     */
    public function getProperty(string $type, string $key, mixed $default = null): mixed
    {
        return Arr::get($this->get($type), $key, $default);
    }

    public function getKind(string $type): string
    {
        return $this->getProperty($type, 'kind');
    }

    public function getLabel(string $type): string
    {
        return $this->getProperty($type, 'label', $type);
    }

    public function getModel(string $type): ?string
    {
        return $this->getProperty($type, 'model');
    }

    public function getJob(string $type): ?string
    {
        return $this->getProperty($type, 'job');
    }

    public function getImporter(string $type): ?string
    {
        return $this->getProperty($type, 'importer');
    }

    public function getExporter(string $type): ?string
    {
        return $this->getProperty($type, 'exporter');
    }

        public function shouldBroadcast(string $type): bool
    {
        return (bool) $this->getProperty($type, 'broadcast.enabled', false);
    }

    public function getBroadcastDriver(string $type): string
    {
        return $this->getProperty($type, 'broadcast.driver', config('broadcasting.default'));
    }

    public function getBroadcastChannel(string $type, int $userId): string
    {
        $channel = $this->getProperty($type, 'broadcast.channel', 'job-status.{userId}');
        return str_replace('{userId}', $userId, $channel);
    }

    public function getBroadcastEvent(string $type): string
    {
        return $this->getProperty($type, 'broadcast.event', 'JobStatusUpdated');
    }


    /**
     * Flatten grouped kind => [type => meta] into type => [kind + meta].
     */
    protected function flatten(array $grouped): array
    {
        $flat = [];

        foreach ($grouped as $kind => $types) {
            foreach ($types as $type => $meta) {
                $flat[$type] = array_merge($meta, ['kind' => $kind]);
            }
        }

        return $flat;
    }

}
