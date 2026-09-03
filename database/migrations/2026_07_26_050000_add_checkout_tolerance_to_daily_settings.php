<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_attendance_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('check_out_tolerance_minutes')->default(0)->after('check_out_start');
        });
    }

    public function down(): void
    {
        Schema::table('daily_attendance_settings', function (Blueprint $table) { $table->dropColumn('check_out_tolerance_minutes'); });
    }
};
