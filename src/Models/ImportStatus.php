<?php

namespace CodedSultan\JobEngine\Models;

use Illuminate\Database\Eloquent\Model;

class JobStatus extends Model
{
    protected $fillable = [
        'user_id', 'kind', 'type', 'total', 'processed',
        'status', 'strategy', 'message',
    ];

}
