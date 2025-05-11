<?php

namespace CodedSultan\JobEngine\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractJobStatus extends Model
{
    use HasUlids;
    protected $table = 'job_statuses';

    protected $fillable = [
        'actor_id',
        'actor_type',
        'kind',
        'type',
        'total',
        'processed',
        'failed',
        'succeeded',
        'status',
        'strategy',
        'message',
        'laravel_job_id',
    ];

    protected $casts = [
        'total' => 'integer',
        'processed' => 'integer',
        'successful' => 'integer',
        'failed' => 'integer',
    ];

    // public function user()
    // {
    //     return $this->belongsTo(config('auth.providers.users.model'));
    // }

    public function actor()
    {
        return $this->morphTo();
    }

    // public function failures()
    // {
    //     if (!isset($this->type, $this->kind)) {
    //         // Return an empty hasMany to avoid breaking eager loads
    //         return $this->hasMany(\Illuminate\Database\Eloquent\Model::class, 'job_status_id')->whereRaw('1=0');
    //     }


    //     $model = \CodedSultan\JobEngine\Support\JobModelResolver::resolve(
    //         $this->type,
    //         $this->kind,
    //         'failure'
    //     );

    //     return $this->hasMany($model, 'job_status_id');
    // }


}
// $job = \App\Models\JobStatus::find('01jtqpacv6dd58ymfvgcm7sn0c')
