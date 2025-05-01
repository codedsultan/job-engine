<?php

namespace CodedSultan\JobEngine\Models;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractJobFailure extends Model
{
    protected $table = 'job_failures';

    protected $fillable = [
        'job_status_id',
        'payload',
        'message',
        'row_identifier',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function jobStatus()
    {
        return $this->belongsTo(config('jobs.status_model'));
    }
}
