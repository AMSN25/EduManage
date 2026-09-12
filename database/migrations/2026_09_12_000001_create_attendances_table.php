<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes');
            $table->foreignId('section_id')->constrained('sections');
            $table->date('date');
            $table->foreignId('taken_by')->constrained('users');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->timestamps();

            $table->unique(['institute_id', 'class_id', 'section_id', 'date']);
            $table->index(['institute_id', 'class_id', 'section_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
