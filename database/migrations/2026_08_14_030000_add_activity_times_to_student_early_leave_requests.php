<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_early_leave_requests', function (Blueprint $table) {
            $table->time('activity_start_time')->nullable()->after('activity_name');
            $table->time('activity_end_time')->nullable()->after('activity_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_early_leave_requests', function (Blueprint $table) {
            $table->dropColumn(['activity_start_time', 'activity_end_time']);
        });
    }
};
