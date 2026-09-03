<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_daily_attendances', function (Blueprint $table) {
            $table->string('check_out_verification_method')->nullable()->after('check_out_status');
            $table->foreignId('check_out_verified_by')->nullable()->after('check_out_verification_method')->constrained('users')->nullOnDelete();
            $table->timestamp('check_out_verified_at')->nullable()->after('check_out_verified_by');
            $table->text('check_out_verification_note')->nullable()->after('check_out_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('student_daily_attendances', function (Blueprint $table) {
            $table->dropForeign(['check_out_verified_by']);
            $table->dropColumn(['check_out_verification_method', 'check_out_verified_by', 'check_out_verified_at', 'check_out_verification_note']);
        });
    }
};
