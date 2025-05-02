<?php

namespace App\Models;

use CodedSultan\JobEngine\Models\AbstractJobFailure;

class ImportFailure extends AbstractJobFailure
{
    protected $table = 'import_failures';

    protected $casts = [
        'payload' => 'array',
    ];
}
