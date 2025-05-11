<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_failures', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('job_status_id')->nullable()->constrained()->nullOnDelete();
            // $table->nullableMorphs('actor');
            $table->ulid('actor_id')->nullable();
            $table->string('actor_type')->nullable();
            $table->json('payload')->nullable();
            $table->text('message');
            $table->boolean('resolved')->default(false);
            $table->boolean('retrying')->default(false);
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamp('last_retried_at')->nullable();
            $table->string('row_identifier')->nullable();
            // $table->json('attempts')->nullable();
            $table->json('job_failure_attempts')->nullable();
            $table->timestamps();

            $table->index(['actor_id', 'actor_type']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_failures');
    }
};
