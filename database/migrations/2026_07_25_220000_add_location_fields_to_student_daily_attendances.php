<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_daily_attendances', function (Blueprint $table) {
            $table->decimal('check_in_latitude', 10, 7)->nullable()->after('check_in_photo');
            $table->decimal('check_in_longitude', 10, 7)->nullable()->after('check_in_latitude');
            $table->decimal('check_in_accuracy', 10, 2)->nullable()->after('check_in_longitude');
            $table->decimal('check_in_distance_meters', 10, 2)->nullable()->after('check_in_accuracy');
            $table->decimal('check_out_latitude', 10, 7)->nullable()->after('check_out_photo');
            $table->decimal('check_out_longitude', 10, 7)->nullable()->after('check_out_latitude');
            $table->decimal('check_out_accuracy', 10, 2)->nullable()->after('check_out_longitude');
            $table->decimal('check_out_distance_meters', 10, 2)->nullable()->after('check_out_accuracy');
        });
    }

    public function down(): void
    {
        Schema::table('student_daily_attendances', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_latitude', 'check_in_longitude', 'check_in_accuracy', 'check_in_distance_meters',
                'check_out_latitude', 'check_out_longitude', 'check_out_accuracy', 'check_out_distance_meters',
            ]);
        });
    }
};
