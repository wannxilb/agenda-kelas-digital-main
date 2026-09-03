<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // DROP/ADD CONSTRAINT hanya didukung PostgreSQL. Driver lain
        // (mis. SQLite untuk test E2E) tidak memiliki check constraint terpisah.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_status_check');
            DB::statement("ALTER TABLE attendances ADD CONSTRAINT attendances_status_check CHECK (status IN ('present', 'absent', 'late', 'excused', 'sick', 'unknown'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_status_check');
            DB::statement("ALTER TABLE attendances ADD CONSTRAINT attendances_status_check CHECK (status IN ('present', 'absent', 'late', 'excused', 'sick'))");
        }
    }
};
