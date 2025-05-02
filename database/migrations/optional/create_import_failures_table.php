<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('import_failures', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('job_status_id')->constrained('import_statuses')->cascadeOnDelete();
            $table->json('payload')->nullable();
            $table->text('message');
            $table->string('row_identifier')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_failures');
    }
};
