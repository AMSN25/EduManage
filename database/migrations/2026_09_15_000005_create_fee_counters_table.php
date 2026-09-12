<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('counter')->default(0);
            $table->timestamps();

            $table->unique(['institute_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_counters');
    }
};
