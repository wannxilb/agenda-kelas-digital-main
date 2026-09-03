<?php

namespace App\Support;

use App\Jobs\SendDailyAttendanceWhatsappNotification;
use App\Models\DailyAttendanceSetting;
use App\Models\StudentDailyAttendance;
use App\Models\StudentEarlyLeaveRequest;
use App\Models\User;
use App\Models\WhatsappNotificationLog;
use Carbon\Carbon;
use Throwable;

class DailyAttendanceWhatsappNotifier
{
    public function sendEarlyLeaveDecision(StudentEarlyLeaveRequest $request, string $decision): void
    {
        $student = $request->student()->first();
        if (! $student || ! $student->parent_phone) {
            return;
        }

        $institutionId = $request->institution_id ?? $student->institution_id;
        if (! $institutionId) {
            return;
        }

        $setting = DailyAttendanceSetting::forInstitution($institutionId);

        if (! $setting->whatsapp_enabled) {
            return;
        }

        if ($setting->isOnlyAbsentWhatsappMode()) {
            return;
        }

        $event = 'early_leave_'.$decision;

        $alreadyLogged = WhatsappNotificationLog::where('early_leave_request_id', $request->id)
            ->where('event_type', $event)
            ->where('recipient_phone', $student->parent_phone)
            ->exists();

        if ($alreadyLogged) {
            return;
        }

        $message = $decision === 'approved'
            ? sprintf('Pengajuan %s %s telah DISETUJUI oleh wali kelas.', $request->categoryLabel(), $this->rangeLabel($request))
            : sprintf('Pengajuan %s %s DITOLAK oleh wali kelas. Alasan: %s.', $request->categoryLabel(), $this->rangeLabel($request), $request->reviewer_note ?? '-');

        $log = WhatsappNotificationLog::create([
            'early_leave_request_id' => $request->id,
            'student_id' => $student->id,
            'recipient_phone' => $student->parent_phone,
            'event_type' => $event,
            'message' => $message,
            'status' => 'pending',
            'attempts' => 0,
            'institution_id' => $institutionId,
        ]);

        SendDailyAttendanceWhatsappNotification::dispatch($log->id);
    }

    public function sendIfEligible(User $student, StudentDailyAttendance $attendance, DailyAttendanceSetting $setting, string $event): void
    {
        if (! $setting->whatsapp_enabled || ! $student->parent_phone || ! $attendance->exists) {
            return;
        }

        if ($setting->isOnlyAbsentWhatsappMode()) {
            return;
        }

        $status = $event === 'check_in'
            ? $attendance->check_in_status
            : $attendance->check_out_status;

        if ($event === 'check_in') {
            $eligibleStatuses = $setting->isEconomyWhatsappMode()
                ? ['late', 'teacher_verified']
                : ['on_time', 'late', 'teacher_verified'];
        } else {
            $eligibleStatuses = $setting->isEconomyWhatsappMode()
                ? ['early', 'teacher_assisted', 'teacher_verified', 'early_with_permission', 'additional_activity']
                : ['early', 'checked_out', 'teacher_assisted', 'teacher_verified', 'early_with_permission', 'additional_activity'];
        }

        if (! in_array($status, $eligibleStatuses, true)) {
            return;
        }

        $alreadyLogged = WhatsappNotificationLog::where('student_daily_attendance_id', $attendance->id)
            ->where('event_type', $event)
            ->exists();

        if ($alreadyLogged) {
            return;
        }

        $message = DailyAttendanceMessages::render(
            DailyAttendanceMessages::defaultTemplate($setting, $event),
            $student,
            $attendance,
            $event
        );

        $log = WhatsappNotificationLog::create([
            'student_daily_attendance_id' => $attendance->id,
            'student_id' => $student->id,
            'recipient_phone' => $student->parent_phone,
            'event_type' => $event,
            'message' => $message,
            'status' => 'pending',
            'attempts' => 0,
            'institution_id' => $student->institution_id,
        ]);

        SendDailyAttendanceWhatsappNotification::dispatch($log->id);
    }

    public function retry(WhatsappNotificationLog $log): void
    {
        $student = $log->student()->first();

        if (! $student || $log->status !== 'failed') {
            return;
        }

        SendDailyAttendanceWhatsappNotification::dispatch($log->id);
    }

    public function deliverLog(int $logId): void
    {
        $log = WhatsappNotificationLog::with('student')->find($logId);

        if (! $log || ! $log->student) {
            return;
        }

        $setting = DailyAttendanceSetting::forInstitution($log->student->institution_id);
        $this->deliver($log, $log->student, $setting, $log->message);
    }

    private function deliver(WhatsappNotificationLog $log, $student, DailyAttendanceSetting $setting, string $message): void
    {
        $log->update([
            'status' => 'pending',
            'attempts' => ((int) $log->attempts) + 1,
            'last_attempt_at' => now(),
        ]);

        try {
            $result = app(FonnteWhatsappClient::class)->send($setting, $student->parent_phone, $message);
        } catch (Throwable $exception) {
            $result = [
                'ok' => false,
                'process' => null,
                'message_id' => null,
                'detail' => null,
                'error' => $exception->getMessage(),
                'body' => null,
            ];
        }

        $status = $result['ok']
            ? ($result['process'] === 'pending' ? 'queued' : 'sent')
            : 'failed';

        $log->update([
            'status' => $status,
            'sent_at' => $status === 'sent' ? now() : null,
            'error_message' => $result['error'],
            'response_body' => $result['body'],
        ]);
    }

    private function rangeLabel(StudentEarlyLeaveRequest $request): string
    {
        $start = Carbon::parse($request->date)->translatedFormat('d M Y');

        if ($request->date_end && $request->date_end->toDateString() !== $request->date->toDateString()) {
            return $start.' - '.Carbon::parse($request->date_end)->translatedFormat('d M Y');
        }

        return $start;
    }
}
