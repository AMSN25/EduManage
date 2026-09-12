<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->string('room_name');
            $table->integer('capacity');
            $table->integer('rows')->default(5);
            $table->integer('columns')->default(5);
            $table->timestamps();

            $table->unique(['institute_id', 'exam_id', 'room_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_rooms');
    }
};
