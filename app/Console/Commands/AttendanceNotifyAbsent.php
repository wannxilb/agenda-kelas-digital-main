<?php

namespace App\Console\Commands;

use App\Jobs\SendDailyAttendanceWhatsappNotification;
use App\Models\DailyAttendanceSetting;
use App\Models\Institution;
use App\Models\Setting;
use App\Models\StudentEarlyLeaveRequest;
use App\Models\User;
use App\Models\WhatsappNotificationLog;
use App\Services\AttendanceStatusResolver;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AttendanceNotifyAbsent extends Command
{
    protected $signature = 'attendance:notify-absent';

    protected $description = 'Kirim notifikasi WhatsApp ke orang tua siswa yang dianggap alpha (tanpa kabar sampai lewat jam verifikasi) hari ini.';

    public function handle(): int
    {
        $today = now()->toDateString();
        $queued = 0;
        $skippedInstitutions = 0;

        $institutions = Institution::where('status', 'active')->get();

        foreach ($institutions as $institution) {
            if (! $this->isOperationalDay($institution->id)) {
                $skippedInstitutions++;

                continue;
            }

            $setting = DailyAttendanceSetting::forInstitution($institution->id);
            if (! $setting->whatsapp_enabled) {
                $skippedInstitutions++;

                continue;
            }

            if (! $this->hasPassedVerificationDeadline($setting, $today)) {
                $skippedInstitutions++;

                continue;
            }

            $queued += $this->notifyInstitution($institution->id, $today);
        }

        if ($queued > 0) {
            $this->info("Notifikasi absen terkirim: {$queued} pesan diantrekan ({$skippedInstitutions} institusi dilewati).");
        }

        return Command::SUCCESS;
    }

    private function notifyInstitution(int $institutionId, string $today): int
    {
        $setting = DailyAttendanceSetting::forInstitution($institutionId);

        $coveredStudentIds = StudentEarlyLeaveRequest::where('institution_id', $institutionId)
            ->where('status', 'pending')
            ->where('date', '<=', $today)
            ->whereRaw('COALESCE(date_end, date) >= ?', [$today])
            ->pluck('student_id')
            ->all();

        $alreadyNotified = WhatsappNotificationLog::where('institution_id', $institutionId)
            ->where('event_type', 'absent')
            ->where('created_at', '>=', Carbon::parse($today)->startOfDay()->toDateTimeString())
            ->where('created_at', '<', Carbon::parse($today)->endOfDay()->addDay()->toDateTimeString())
            ->pluck('student_id')
            ->all();

        $resolver = app(AttendanceStatusResolver::class);

        $students = User::role('siswa')
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->whereNotNull('class_id')
            ->with('class')
            ->get();

        $queued = 0;

        foreach ($students as $student) {
            if (! $student->parent_phone) {
                continue;
            }

            if (in_array($student->id, $coveredStudentIds, true)
                || in_array($student->id, $alreadyNotified, true)) {
                continue;
            }

            if ($resolver->resolve($student->id, $today) !== 'absent') {
                continue;
            }

            $message = strtr(
                $setting->absent_message_template
                    ?: 'Ananda {student} ({class}) tidak tercatat hadir di sekolah hari ini, {date}. Jika anak berhalangan, mohon sampaikan keterangan ke wali kelas.',
                [
                    '{student}' => $student->name,
                    '{class}' => $student->class?->name ?? '-',
                    '{date}' => Carbon::parse($today)->translatedFormat('d M Y'),
                ]
            );

            $log = WhatsappNotificationLog::create([
                'student_id' => $student->id,
                'recipient_phone' => $student->parent_phone,
                'event_type' => 'absent',
                'message' => $message,
                'status' => 'pending',
                'attempts' => 0,
                'institution_id' => $institutionId,
            ]);

            SendDailyAttendanceWhatsappNotification::dispatch($log->id);
            $queued++;
        }

        return $queued;
    }

    private function isOperationalDay(?int $institutionId): bool
    {
        $overrideUntil = Setting::get('operational_override_until', null, $institutionId);
        if ($overrideUntil && Carbon::parse($overrideUntil)->isFuture()) {
            return true;
        }

        $operationalDays = array_filter(explode(',', Setting::get('operational_days', '1,2,3,4,5', $institutionId)));

        return in_array((string) now()->dayOfWeekIso, $operationalDays, true);
    }

    private function hasPassedVerificationDeadline(DailyAttendanceSetting $setting, string $today): bool
    {
        $deadline = Carbon::parse($today.' '.$setting->check_in_verification_deadline);

        return now()->greaterThan($deadline);
    }
}
