<?php

namespace App\Models;

use CodedSultan\JobEngine\Models\AbstractJobFailure;

class ExportFailure extends AbstractJobFailure
{
    protected $table = 'export_failures';

    protected $casts = [
        'payload' => 'array',
    ];
}
