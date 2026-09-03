<?php

namespace App\Services;

use App\Models\DailyAttendanceSetting;
use App\Models\Institution;
use App\Models\User;
use App\Models\WhatsappNotificationLog;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;
use Throwable;

/**
 * Mengevaluasi kesiapan pipeline notifikasi ketidakhadiran (alpha) agar admin
 * bisa langsung melihat penyebab notifikasi tidak terkirim. Hanya untuk inspeksi
 * UI — tidak mengubah data apa pun.
 */
class WhatsappNotificationHealth
{
    public function overview(int $institutionId): array
    {
        $setting = DailyAttendanceSetting::forInstitution($institutionId);

        /** @var User|false|null */
        $admin = \Illuminate\Support\Facades\Auth::user();

        $totalStudents = $this->countActiveStudents($institutionId);
        $withPhone = $this->countActiveStudentsWithPhone($institutionId);
        $noPhone = $totalStudents - $withPhone;

        $checks = [
            'whatsapp_enabled' => $this->check('Aktifkan WhatsApp', $setting->whatsapp_enabled, 'Aktifasi notifikasi dimatikan di pengaturan.'),
            'token' => $this->check('Token Fonnte', trim((string) $setting->whatsapp_token) !== '', 'Token WhatsApp Fonnte belum diisi.'),
            'api_url' => $this->check('URL API Fonnte', trim((string) $setting->whatsapp_api_url) !== '', 'URL API Fonnte belum diisi.'),
            'scheduler' => $this->checkScheduler(),
            'phone' => $this->check(
                'Nomor WA orang tua',
                $withPhone > 0,
                'Tidak ada siswa dengan nomor WA orang tua. '
                    . "({$noPhone} dari {$totalStudents} siswa belum mengisi nomor.)",
                $withPhone > 0 && $noPhone > 0
                    ? "{$withPhone} dari {$totalStudents} siswa punya nomor ({$noPhone} belum)."
                    : "{$withPhone} dari {$totalStudents} siswa punya nomor."
            ),
        ];

        $deadline = Carbon::parse(now()->toDateString().' '.($setting->check_in_verification_deadline ?? '23:59'));

        $todayActive = $this->countTodayLogs($institutionId, 'absent');

        return [
            'setting' => $setting,
            'checks' => $checks,
            'all_pass' => collect($checks)->every(fn ($c) => $c['ok'] === true),
            'total_students' => $totalStudents,
            'with_phone' => $withPhone,
            'no_phone' => $noPhone,
            'deadline' => $deadline->format('H:i'),
            'now' => now(),
            'deadline_passed' => now()->greaterThan($deadline),
            'today_absent_logs' => $todayActive,
        ];
    }

    private function checkScheduler(): array
    {
        $runsEver = \Illuminate\Support\Facades\File::exists(storage_path('logs/attendance-notify-absent.log'));

        // Heuristik ringan: apakah perintah pernah tercatat jalan. Kami tidak
        // bisa memeriksa proses schedule:work lintas platform, jadi info ini
        // berbasis bukti yang ada di sistem.
        return $this->check(
            'Scheduler aktif',
            $runsEver,
            'Command `attendance:notify-absent` belum pernah terekam berjalan. '
                .'Pastikan `php artisan schedule:work` berjalan di terminal (atau cron di server).',
            $runsEver ? 'Command pernah berjalan (lihat log).' : ''
        );
    }

    private function check(string $label, bool $ok, string $failMessage, string $okMessage = ''): array
    {
        return [
            'ok' => $ok,
            'label' => $label,
            'message' => $ok ? ($okMessage ?: ($label.' siap.')) : $failMessage,
        ];
    }

    private function countActiveStudents(int $institutionId): int
    {
        return User::role('siswa')
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->whereNotNull('class_id')
            ->count();
    }

    private function countActiveStudentsWithPhone(int $institutionId): int
    {
        return User::role('siswa')
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->whereNotNull('class_id')
            ->whereNotNull('parent_phone')
            ->where('parent_phone', '!=', '')
            ->count();
    }

    private function countTodayLogs(int $institutionId, string $eventType): int
    {
        $today = now()->toDateString();

        return WhatsappNotificationLog::where('institution_id', $institutionId)
            ->where('event_type', $eventType)
            ->whereDate('created_at', $today)
            ->count();
    }
}
