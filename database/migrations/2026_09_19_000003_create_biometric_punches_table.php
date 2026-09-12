<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biometric_punches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('biometric_device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institute_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('device_user_id');
            $table->timestamp('punch_time');
            $table->enum('punch_state', ['check_in', 'check_out'])->default('check_in');
            $table->enum('resolution_status', ['pending', 'resolved', 'device_pending', 'no_mapping'])->default('pending');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['biometric_device_id', 'punch_time']);
            $table->index(['institute_id', 'resolution_status']);
            $table->index('resolution_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_punches');
    }
};
