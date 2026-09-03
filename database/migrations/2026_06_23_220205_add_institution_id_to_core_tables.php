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
        $tables = ['classes', 'class_histories', 'rooms', 'schedules', 'agendas', 'attendances', 'attendance_locks', 'subjects'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBuilder) {
                $tableBuilder->foreignId('institution_id')->nullable()->constrained('institutions')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['classes', 'class_histories', 'rooms', 'schedules', 'agendas', 'attendances', 'attendance_locks', 'subjects'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBuilder) {
                $tableBuilder->dropForeign(['institution_id']);
                $tableBuilder->dropColumn('institution_id');
            });
        }
    }
};
