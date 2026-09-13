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
        Schema::table('daily_attendance_settings', function (Blueprint $table) {
            $table->string('absent_message_template')->nullable()->after('check_out_message_template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_attendance_settings', function (Blueprint $table) {
            $table->dropColumn('absent_message_template');
        });
    }
};