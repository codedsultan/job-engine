<?php

namespace CodedSultan\JobEngine\Listeners;

use CodedSultan\JobEngine\Services\JobRegistry;

abstract class BaseJobListener
{
    protected function shouldBroadcast(string $type): bool
    {
        return app(JobRegistry::class)->shouldBroadcast($type);
    }
}
