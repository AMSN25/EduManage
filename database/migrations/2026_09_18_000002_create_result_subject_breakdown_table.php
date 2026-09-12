<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_subject_breakdowns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->decimal('obtained', 7, 2)->default(0);
            $table->decimal('full', 7, 2)->default(0);
            $table->string('grade')->default('F');
            $table->decimal('gpa_point', 3, 1)->default(0);
            $table->timestamps();

            $table->unique(['result_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_subject_breakdowns');
    }
};
