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
        Schema::table('subjects', function (Blueprint $table) {
            // SQLite tidak bisa menghapus kolom yang masih direferensikan index
            // (subjects_code_institution_id_unique dibuat oleh migration
            // add_institution_unique_constraints). Index ini dihapus dulu supaya
            // migration bisa jalan di semua driver (pgsql + sqlite E2E).
            $table->dropUnique('subjects_code_institution_id_unique');
            $table->dropColumn('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('code')->unique()->nullable()->after('id');
        });
    }
};
