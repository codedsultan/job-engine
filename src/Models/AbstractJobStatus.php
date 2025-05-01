<?php

namespace CodedSultan\JobEngine\Models;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractJobStatus extends Model
{
    protected $table = 'job_statuses';

    protected $fillable = [
        'user_id',
        'kind',
        'type',
        'total',
        'processed',
        'status',
        'strategy',
        'message',
        'laravel_job_id',
    ];

    protected $casts = [
        'total' => 'integer',
        'processed' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
