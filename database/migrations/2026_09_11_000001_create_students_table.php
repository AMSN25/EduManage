<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('student_id', 30)->comment('Per-institute sequential e.g. INST001-2026-0001');
            $table->string('admission_no', 30)->nullable();
            $table->unsignedInteger('roll')->nullable();
            $table->string('name');
            $table->string('name_bangla')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('dob')->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->string('religion')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('photo_path')->nullable();

            // Academic assignment
            $table->foreignId('class_id')->constrained('classes');
            $table->foreignId('section_id')->constrained('sections');
            $table->foreignId('group_id')->nullable()->constrained('groups');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->date('admission_date');
            $table->enum('status', ['active', 'inactive', 'transferred', 'graduated'])->default('active');

            // Present address
            $table->string('present_village')->nullable();
            $table->string('present_post_office')->nullable();
            $table->string('present_upazila')->nullable();
            $table->string('present_district')->nullable();
            $table->string('present_division')->nullable();

            // Permanent address
            $table->string('permanent_village')->nullable();
            $table->string('permanent_post_office')->nullable();
            $table->string('permanent_upazila')->nullable();
            $table->string('permanent_district')->nullable();
            $table->string('permanent_division')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['institute_id', 'student_id']);
            $table->unique(['institute_id', 'admission_no']);
            $table->index(['institute_id', 'class_id', 'section_id']);
            $table->index(['institute_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
