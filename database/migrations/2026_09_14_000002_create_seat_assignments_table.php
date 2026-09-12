<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seat_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->integer('seat_no');
            $table->timestamps();

            $table->unique(['exam_room_id', 'seat_no']);
            $table->unique(['exam_room_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seat_assignments');
    }
};
