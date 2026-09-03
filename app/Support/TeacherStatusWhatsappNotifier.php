<?php

namespace App\Support;

use App\Jobs\SendTeacherStatusNotification;
use App\Models\DailyAttendanceSetting;
use App\Models\TeacherStatus;
use App\Models\User;
use App\Models\WhatsappNotificationLog;
use Carbon\Carbon;
use Throwable;

class TeacherStatusWhatsappNotifier
{
    public function notifySubmission(TeacherStatus $status): void
    {
        $message = sprintf(
            '%s mengajukan %s %s. Alasan: %s. Mohon review di aplikasi.',
            $status->teacher?->name ?? 'Guru',
            $status->typeLabel(),
            $this->rangeLabel($status),
            $status->note ?? '-'
        );

        $this->notifyWakaseks($status, 'teacher_status_submitted', $message);
    }

    public function notifyUpdated(TeacherStatus $status): void
    {
        $message = sprintf(
            '%s memperbarui pengajuan %s %s. Alasan: %s. Mohon review ulang di aplikasi.',
            $status->teacher?->name ?? 'Guru',
            $status->typeLabel(),
            $this->rangeLabel($status),
            $status->note ?? '-'
        );

        $this->notifyWakaseks($status, 'teacher_status_updated', $message);
    }

    public function notifyApproved(TeacherStatus $status): void
    {
        $this->notifyTeacher($status, 'teacher_status_approved', sprintf(
            'Pengajuan %s tanggal %s telah DISETUJUI.',
            $status->typeLabel(),
            $this->rangeLabel($status)
        ));
    }

    public function notifyRejected(TeacherStatus $status): void
    {
        $this->notifyTeacher($status, 'teacher_status_rejected', sprintf(
            'Pengajuan %s tanggal %s DITOLAK. Alasan: %s.',
            $status->typeLabel(),
            $this->rangeLabel($status),
            $status->rejection_reason ?? '-'
        ));
    }

    public function notifyCancelled(TeacherStatus $status): void
    {
        $this->notifyTeacher($status, 'teacher_status_cancelled', sprintf(
            'Pengajuan %s tanggal %s DIBATALKAN. Alasan: %s.',
            $status->typeLabel(),
            $this->rangeLabel($status),
            $status->cancellation_reason ?? '-'
        ));
    }

    private function notifyWakaseks(TeacherStatus $status, string $event, string $message): void
    {
        $setting = DailyAttendanceSetting::forInstitution($status->institution_id);

        if (! $setting->whatsapp_enabled) {
            return;
        }

        $wakaseks = User::role('wakasek')
            ->where('institution_id', $status->institution_id)
            ->where('status', 'active')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get();

        foreach ($wakaseks as $wakasek) {
            $this->createLog($status, $wakasek->phone, $event, $message);
        }
    }

    private function notifyTeacher(TeacherStatus $status, string $event, string $message): void
    {
        $setting = DailyAttendanceSetting::forInstitution($status->institution_id);

        if (! $setting->whatsapp_enabled) {
            return;
        }

        $teacher = $status->teacher;
        if (! $teacher || ! $teacher->phone) {
            return;
        }

        $this->createLog($status, $teacher->phone, $event, $message);
    }

    private function createLog(TeacherStatus $status, string $phone, string $event, string $message): void
    {
        $alreadyLogged = WhatsappNotificationLog::where('teacher_status_id', $status->id)
            ->where('event_type', $event)
            ->where('recipient_phone', $phone)
            ->exists();

        if ($alreadyLogged) {
            return;
        }

        $log = WhatsappNotificationLog::create([
            'teacher_status_id' => $status->id,
            'recipient_phone' => $phone,
            'event_type' => $event,
            'message' => $message,
            'status' => 'pending',
            'attempts' => 0,
            'institution_id' => $status->institution_id,
        ]);

        SendTeacherStatusNotification::dispatch($log->id);
    }

    public function deliverLog(int $logId): void
    {
        $log = WhatsappNotificationLog::with('teacherStatus')->find($logId);

        if (! $log || ! $log->teacher_status_id) {
            return;
        }

        $setting = DailyAttendanceSetting::forInstitution($log->institution_id);

        if (! $setting->whatsapp_enabled) {
            return;
        }

        $log->update([
            'status' => 'pending',
            'attempts' => ((int) $log->attempts) + 1,
            'last_attempt_at' => now(),
        ]);

        try {
            $result = app(FonnteWhatsappClient::class)->send($setting, $log->recipient_phone, $log->message);
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

    private function rangeLabel(TeacherStatus $status): string
    {
        $start = Carbon::parse($status->date)->translatedFormat('d M Y');

        if ($status->date_end && $status->date_end->toDateString() !== $status->date->toDateString()) {
            return $start.' - '.Carbon::parse($status->date_end)->translatedFormat('d M Y');
        }

        return $start;
    }
}
