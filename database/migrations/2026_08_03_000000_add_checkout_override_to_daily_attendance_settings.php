<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_attendance_settings', function (Blueprint $table) {
            $table->date('check_out_override_date')->nullable()->after('check_out_start');
            $table->time('check_out_override_time')->nullable()->after('check_out_override_date');
        });
    }

    public function down(): void
    {
        Schema::table('daily_attendance_settings', function (Blueprint $table) {
            $table->dropColumn(['check_out_override_date', 'check_out_override_time']);
        });
    }
};
