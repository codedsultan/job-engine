<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_statuses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            // $table->foreignUlid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->ulid('actor_id')->nullable();
            $table->string('actor_type')->nullable();
            $table->string('kind');
            $table->string('type');
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('processed')->default(0);
            $table->unsignedInteger('successful')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->string('status')->default('pending');
            $table->string('strategy')->default('polling');
            $table->text('message')->nullable();
            $table->uuid('laravel_job_id')->nullable();
            $table->timestamps();

            $table->index(['actor_id', 'actor_type']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_statuses');
    }
};
