<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institute_settings', function (Blueprint $table) {
            $table->string('sms_provider', 20)->default('log')->after('current_academic_year');
            $table->string('sms_api_url')->nullable()->after('sms_provider');
            $table->string('sms_api_key')->nullable()->after('sms_api_url');
            $table->string('sms_sender_id', 30)->nullable()->after('sms_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('institute_settings', function (Blueprint $table) {
            $table->dropColumn(['sms_provider', 'sms_api_url', 'sms_api_key', 'sms_sender_id']);
        });
    }
};
