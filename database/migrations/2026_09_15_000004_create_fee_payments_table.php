<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_fee_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'manual_online'])->default('cash');
            $table->string('receipt_no', 30);
            $table->foreignId('collected_by')->constrained('users');
            $table->timestamp('paid_at');
            $table->timestamps();

            $table->unique(['institute_id', 'receipt_no']);
            $table->index('receipt_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
