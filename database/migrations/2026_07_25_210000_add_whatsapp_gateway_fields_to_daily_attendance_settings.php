<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_attendance_settings', function (Blueprint $table) {
            $table->string('whatsapp_provider')->default('fonnte')->after('whatsapp_enabled');
            $table->string('whatsapp_api_url')->default('https://api.fonnte.com/send')->after('whatsapp_provider');
            $table->string('whatsapp_token')->nullable()->after('whatsapp_api_url');
            $table->string('whatsapp_country_code', 5)->default('62')->after('whatsapp_token');
        });

        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            $table->longText('response_body')->nullable()->after('error_message');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            $table->dropColumn('response_body');
        });

        Schema::table('daily_attendance_settings', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_provider',
                'whatsapp_api_url',
                'whatsapp_token',
                'whatsapp_country_code',
            ]);
        });
    }
};
