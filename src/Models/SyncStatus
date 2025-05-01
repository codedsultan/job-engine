<?php

namespace App\Models;

use CodedSultan\JobEngine\Models\AbstractJobStatus;

class ImportStatus extends AbstractJobStatus
{
    protected $table = 'import_statuses'; // Separate table

    protected $casts = [
        'total' => 'integer',
        'processed' => 'integer',
        'metadata' => 'array',
    ];
}
