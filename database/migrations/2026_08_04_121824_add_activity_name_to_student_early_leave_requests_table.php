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
            $table->string('activity_name')->nullable()->after('reason');
            $table->dropColumn('subject_teacher_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_early_leave_requests', function (Blueprint $table) {
            $table->string('subject_teacher_name')->nullable()->after('reason');
            $table->dropColumn('activity_name');
        });
    }
};
