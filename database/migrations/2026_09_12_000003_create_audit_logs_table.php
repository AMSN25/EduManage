<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('action'); // e.g. 'attendance.updated', 'fee.created'
            $table->string('subject_type')->nullable(); // e.g. 'App\Models\Attendance'
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('changes')->nullable(); // {field: [old, new]}
            $table->timestamps();

            $table->index(['institute_id', 'subject_type', 'subject_id']);
            $table->index(['institute_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
