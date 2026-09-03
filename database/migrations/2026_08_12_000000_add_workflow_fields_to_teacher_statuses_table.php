<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * V2 workflow izin/tugas luar guru.
     *
     * - `type`   : izin | tugas_luar (nilai LAMA kolom `status` dipindah ke sini)
     * - `status` : pending | approved | rejected | cancelled (workflow baru)
     *
     * Backfill: record lama (status='izin'|'tugas_luar') dianggap SUDAH
     * DISETUJUI supaya monitoring tidak kehilangan data yang sedang berjalan
     * (lihat docs/design-izin-tugas-luar-guru.md §5.4).
     */
    public function up(): void
    {
        Schema::table('teacher_statuses', function (Blueprint $table) {
            $table->string('type')->nullable()->after('teacher_id');
            $table->date('date_end')->nullable()->after('date');
            $table->time('start_time')->nullable()->after('date_end');
            $table->time('end_time')->nullable()->after('start_time');
            $table->string('attachment')->nullable()->after('note');
            $table->text('rejection_reason')->nullable()->after('attachment');
            $table->foreignId('approver_id')->nullable()->after('rejection_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable()->after('approver_id');
            $table->timestamp('cancelled_at')->nullable()->after('processed_at');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            $table->foreignId('substitute_teacher_id')->nullable()->after('cancelled_by')->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable()->after('substitute_teacher_id');
        });

        // Backfill data lama: type = status lama, status → approved (menjaga
        // kontinuitas monitoring), processed_at mengikuti updated_at/created_at.
        DB::table('teacher_statuses')
            ->whereIn('status', ['izin', 'tugas_luar'])
            ->update([
                'type' => DB::raw('status'),
                'status' => 'approved',
                'processed_at' => DB::raw('COALESCE(updated_at, CURRENT_TIMESTAMP)'),
            ]);

        Schema::table('teacher_statuses', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
            $table->index(['teacher_id', 'status']);
            $table->index('date');
            $table->index('date_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_statuses', function (Blueprint $table) {
            $table->dropIndex(['teacher_id', 'status']);
            $table->dropIndex(['date']);
            $table->dropIndex(['date_end']);
        });

        Schema::table('teacher_statuses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approver_id');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropConstrainedForeignId('substitute_teacher_id');
        });

        Schema::table('teacher_statuses', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'date_end',
                'start_time',
                'end_time',
                'attachment',
                'rejection_reason',
                'processed_at',
                'cancelled_at',
                'cancellation_reason',
            ]);
        });
    }
};
