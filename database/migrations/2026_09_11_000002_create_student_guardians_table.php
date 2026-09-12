<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('relation', ['father', 'mother', 'guardian']);
            $table->string('name');
            $table->string('phone', 20);
            $table->string('occupation')->nullable();
            $table->string('nid', 20)->nullable()->comment('National ID');
            $table->timestamps();

            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_guardians');
    }
};
