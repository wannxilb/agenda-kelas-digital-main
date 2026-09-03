<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_daily_attendance_corrections', function (Blueprint $table) {
            $table->string('evidence_mime')->nullable()->after('evidence_path');
        });
    }

    public function down(): void
    {
        Schema::table('student_daily_attendance_corrections', function (Blueprint $table) {
            $table->dropColumn('evidence_mime');
        });
    }
};
