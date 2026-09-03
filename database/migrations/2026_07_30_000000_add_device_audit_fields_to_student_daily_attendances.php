<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_daily_attendances', function (Blueprint $table) {
            $table->string('check_in_ip_address', 45)->nullable()->after('check_in_distance_meters');
            $table->text('check_in_user_agent')->nullable()->after('check_in_ip_address');
            $table->string('check_in_device_fingerprint', 128)->nullable()->after('check_in_user_agent');
            $table->boolean('check_in_suspicious')->default(false)->after('check_in_device_fingerprint');
            $table->text('check_in_suspicious_reason')->nullable()->after('check_in_suspicious');

            $table->string('check_out_ip_address', 45)->nullable()->after('check_out_distance_meters');
            $table->text('check_out_user_agent')->nullable()->after('check_out_ip_address');
            $table->string('check_out_device_fingerprint', 128)->nullable()->after('check_out_user_agent');
            $table->boolean('check_out_suspicious')->default(false)->after('check_out_device_fingerprint');
            $table->text('check_out_suspicious_reason')->nullable()->after('check_out_suspicious');
        });
    }

    public function down(): void
    {
        Schema::table('student_daily_attendances', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_ip_address',
                'check_in_user_agent',
                'check_in_device_fingerprint',
                'check_in_suspicious',
                'check_in_suspicious_reason',
                'check_out_ip_address',
                'check_out_user_agent',
                'check_out_device_fingerprint',
                'check_out_suspicious',
                'check_out_suspicious_reason',
            ]);
        });
    }
};
