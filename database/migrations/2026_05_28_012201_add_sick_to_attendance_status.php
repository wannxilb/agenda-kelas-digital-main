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
        // DROP/ADD CONSTRAINT hanya didukung PostgreSQL. SQLite (DB test E2E)
        // tidak mendukung sintaks ini, dan tidak ada check constraint terpisah
        // yang perlu di-update — jadi statement ini di-skip untuk driver lain.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_status_check');
            DB::statement("ALTER TABLE attendances ADD CONSTRAINT attendances_status_check CHECK (status IN ('present', 'absent', 'late', 'excused', 'sick'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_status_check');
            DB::statement("ALTER TABLE attendances ADD CONSTRAINT attendances_status_check CHECK (status IN ('present', 'absent', 'late', 'excused'))");
        }
    }
};
