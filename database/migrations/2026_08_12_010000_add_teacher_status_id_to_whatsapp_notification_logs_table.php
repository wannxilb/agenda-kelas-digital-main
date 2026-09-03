<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Notifikasi WhatsApp untuk izin/tugas luar guru.
     *
     * Kolom `student_daily_attendance_id` pada unique index lama
     * (whatsapp_logs_attendance_event_unique) akan NULL di sini, sehingga tanpa
     * index baru job retry (tries: 3) bisa mengirim pesan ganda.
     *
     * Unique index (teacher_status_id, event_type, recipient_phone) mencegah
     * duplikat PER PENERIMA — karena satu pengajuan dikirim ke SEMUA wakasek
     * (keputusan desain §16.8), beberapa log sah berbagi (teacher_status_id,
     * event_type) yang sama selama recipient_phone-nya berbeda. Index yang
     * menyertakan recipient_phone menjaga retry tidak mengirim ganda ke orang
     * yang sama, tanpa memblokir notifikasi ke wakasek lain.
     */
    public function up(): void
    {
        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            $table->foreignId('teacher_status_id')
                ->nullable()
                ->after('student_daily_attendance_id')
                ->constrained('teacher_statuses')
                ->nullOnDelete();

            $table->unique(
                ['teacher_status_id', 'event_type', 'recipient_phone'],
                'whatsapp_logs_teacher_status_event_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            $table->dropUnique('whatsapp_logs_teacher_status_event_unique');
        });

        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_status_id');
        });
    }
};
