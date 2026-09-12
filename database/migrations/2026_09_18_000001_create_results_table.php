<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_obtained', 7, 2)->default(0);
            $table->decimal('total_full', 7, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('gpa', 3, 2)->default(0);
            $table->string('grade')->default('F');
            $table->unsignedBigInteger('position')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();

            $table->unique(['institute_id', 'exam_id', 'student_id']);
            $table->index(['institute_id', 'exam_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
