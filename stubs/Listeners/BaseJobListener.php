<?php

namespace App\Listeners\JobEngine;

use CodedSultan\JobEngine\Support\BroadcastConfigHelper;

abstract class BaseJobListener
{
    protected function helper(): BroadcastConfigHelper
    {
        return app(BroadcastConfigHelper::class);
    }
}
