<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Notifikasi WhatsApp untuk hasil review pengajuan izin/dispensasi siswa.
     *
     * Kolom `student_daily_attendance_id` pada unique index lama
     * (whatsapp_logs_attendance_event_unique) akan NULL di sini, sehingga tanpa
     * index baru job retry (tries: 3) bisa mengirim pesan ganda.
     *
     * Unique index (early_leave_request_id, event_type, recipient_phone)
     * mencegah duplikat PER PENERIMA saat job retry, mengikuti pola yang sama
     * dengan teacher_status.
     */
    public function up(): void
    {
        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            $table->foreignId('early_leave_request_id')
                ->nullable()
                ->after('student_daily_attendance_id')
                ->constrained('student_early_leave_requests')
                ->nullOnDelete();

            $table->unique(
                ['early_leave_request_id', 'event_type', 'recipient_phone'],
                'whatsapp_logs_early_leave_event_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            $table->dropUnique('whatsapp_logs_early_leave_event_unique');
        });

        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('early_leave_request_id');
        });
    }
};
