<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('obtained_marks', 6, 2)->default(0);
            $table->decimal('cq_obtained', 6, 2)->nullable();
            $table->decimal('mcq_obtained', 6, 2)->nullable();
            $table->decimal('practical_obtained', 6, 2)->nullable();
            $table->boolean('is_absent')->default(false);
            $table->foreignId('entered_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['draft', 'submitted', 'locked'])->default('draft');
            $table->timestamps();

            $table->unique(['exam_subject_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marks');
    }
};
