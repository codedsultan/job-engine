<?php

namespace CodedSultan\JobEngine\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use CodedSultan\JobEngine\Support\JobModelResolver;
abstract class AbstractJobFailure extends Model
{
    use HasUlids;
    protected $table = 'job_failures';

    protected $fillable = [
        'actor_id',
        'actor_type',
        'job_status_id',
        'payload',
        'message',
        'row_identifier',
        'retry_count',
        'last_retried_at',
        'resolved',
        'job_failure_attempts',

    ];

    protected $casts = [
        'payload' => 'array',
        'last_retried_at' => 'datetime',
        'resolved' => 'boolean',
        'job_failure_attempts' => 'array',

    ];

    // public function jobStatus()
    // {
    //     return $this->belongsTo(config('job-engine.status_model'));
    // }


    public function jobStatus()
    {
        $statusModel = JobModelResolver::resolve($this->type ?? 'unknown', $this->kind ?? 'import', 'status');
        return $this->belongsTo($statusModel, 'job_status_id');
    }

    // public function attempts()
    // {
    //     return $this->hasMany(JobFailureAttempt::class);
    // }
    // Schema::create('job_failure_attempts', function (Blueprint $table) {
    //     $table->ulid('id')->primary();
    //     $table->foreignUlid('job_failure_id')->constrained()->cascadeOnDelete();
    //     $table->text('message');
    //     $table->json('payload')->nullable();
    //     $table->timestamp('attempted_at')->useCurrent();
    // });


}
