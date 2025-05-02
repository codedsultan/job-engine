<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_engine_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();   // e.g., 'exports.delivery'
            $table->json('value');             // stored as JSON string
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('job_statuses');
    }
};
