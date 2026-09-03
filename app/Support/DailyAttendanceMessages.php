<?php

namespace App\Support;

use App\Models\DailyAttendanceSetting;
use App\Models\StudentDailyAttendance;
use App\Models\User;

class DailyAttendanceMessages
{
    public static function render(string $template, User $student, StudentDailyAttendance $attendance, string $event): string
    {
        $time = $event === 'check_in'
            ? $attendance->check_in_at?->format('H:i')
            : $attendance->check_out_at?->format('H:i');
        $presentation = $event === 'check_in'
            ? $attendance->checkInPresentation()
            : $attendance->checkOutPresentation();
        $status = collect($presentation)->pluck('label')->filter()->implode(' • ');
        $status = $status !== '' ? $status : '-';

        return strtr($template, [
            '{student}' => $student->name,
            '{class}' => $student->class->name ?? '-',
            '{date}' => $attendance->date?->format('d/m/Y') ?? now()->format('d/m/Y'),
            '{time}' => $time ?? '-',
            '{status}' => $status,
        ]);
    }

    public static function defaultTemplate(DailyAttendanceSetting $setting, string $event): string
    {
        return $event === 'check_in'
            ? ($setting->check_in_message_template ?: 'Ananda {student} telah masuk sekolah pukul {time}. Status: {status}.')
            : ($setting->check_out_message_template ?: 'Ananda {student} telah pulang sekolah pukul {time}. Status: {status}.');
    }

    public static function statusLabel(?string $status, int $minutes = 0): string
    {
        return match ($status) {
            'alpha' => 'Alpha (melebati batas)',
            'late' => 'Terlambat '.$minutes.' menit',
            'needs_verification' => 'Menunggu verifikasi wali kelas',
            'teacher_verified' => 'Disetujui wali kelas',
            'teacher_rejected' => 'Ditolak wali kelas',
            'early' => 'Pulang cepat '.$minutes.' menit',
            'on_time' => 'Tepat waktu',
            'checked_out' => 'Pulang',
            'early_with_permission' => 'Pulang dengan izin',
            'additional_activity' => 'Dispensasi kegiatan',
            'teacher_assisted' => 'Dibantu wali kelas',
            default => '-',
        };
    }
}
