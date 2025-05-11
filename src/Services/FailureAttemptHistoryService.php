<?php

namespace CodedSultan\JobEngine\Services;

use Illuminate\Database\Eloquent\Model;

class FailureAttemptHistoryService
{
    public function recordAttempt(Model $failure, array $attempt): void
    {
        if (method_exists($failure, 'appendToJsonHistory')) {
            $failure->appendToJsonHistory('job_failure_attempts', $attempt);
            $failure->save();
        } else {
            // Fallback for future relational implementation
            // \App\Models\JobFailureAttempt::create([
            //     'job_failure_id' => $failure->id,
            //     'message' => $attempt['message'],
            //     'payload' => $attempt['payload'],
            //     'attempted_at' => $attempt['attempted_at'],
            // ]);
        }
    }

    public function getAttempts(Model $failure): array
    {
        if (method_exists($failure, 'getJsonHistory')) {
            return $failure->getJsonHistory('job_failure_attempts');
        }

        // Fallback to DB table in future
        return $failure->attempts()->get()->toArray();
    }
}
