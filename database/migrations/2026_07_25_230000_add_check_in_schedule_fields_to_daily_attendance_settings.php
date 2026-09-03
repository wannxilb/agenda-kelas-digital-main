<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_attendance_settings', function (Blueprint $table) {
            $table->time('check_in_start')->default('05:00:00')->after('institution_id');
            $table->time('check_in_verification_deadline')->default('08:00:00')->after('check_in_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('daily_attendance_settings', function (Blueprint $table) {
            $table->dropColumn(['check_in_start', 'check_in_verification_deadline']);
        });
    }
};
