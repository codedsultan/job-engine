<?php

namespace CodedSultan\JobEngine\Support;

use CodedSultan\JobEngine\Services\JobRegistry;

class BroadcastConfigHelper
{
    public function __construct(protected JobRegistry $registry) {}

    public function enabled(string $type): bool
    {
        // 1. Global kill switch
        if (! config('job-engine.broadcasting.enabled', true)) {
            return false;
        }

        // 2. Per-job config (can be true or an array)
        $val = $this->registry->getProperty(
            $type,
            'broadcast',
            config('job-engine.listeners.broadcast', true) // 👈 this is fallback
        );

        if (is_bool($val)) return $val;

        if (is_array($val)) {
            return (bool) ($val['enabled'] ?? false);
        }

        return false;
    }


    public function driver(string $type): string
    {
        return $this->registry->getProperty($type, 'broadcast.driver', config('broadcasting.default'));
    }

    public function channel(string $type, int|string $userId): string
    {
        $raw = $this->registry->getProperty($type, 'broadcast.channel', 'job-status.{userId}');
        return str_replace('{userId}', $userId, $raw);
    }

    public function event(string $type): string
    {
        return $this->registry->getProperty($type, 'broadcast.event', 'JobStatusUpdated');
    }

    public function shouldLog(string $type): bool
    {
        return (bool) $this->registry->getProperty($type, 'log', config('job-engine.listeners.log', true));
    }

    public function shouldNotify(string $type): bool
    {
        return (bool) $this->registry->getProperty($type, 'notify', config('job-engine.listeners.notify', true));
    }

}
