<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grading_system_id')->constrained()->cascadeOnDelete();
            $table->decimal('min_percent', 5, 2);
            $table->decimal('max_percent', 5, 2);
            $table->string('grade');
            $table->decimal('gpa_point', 3, 1);
            $table->timestamps();

            $table->unique(['grading_system_id', 'min_percent', 'max_percent']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_ranges');
    }
};
