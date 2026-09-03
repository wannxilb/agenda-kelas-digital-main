<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\DailyAttendanceSetting;
use App\Models\StudentDailyAttendance;
use App\Models\StudentEarlyLeaveRequest;
use Carbon\Carbon;

class DailyAttendancePresensiSync
{
    public function __construct(private AttendanceStatusResolver $resolver) {}

    public function sync(StudentDailyAttendance $dailyAttendance): Attendance
    {
        $dailyAttendance->loadMissing('student');

        $date = $dailyAttendance->date->toDateString();
        $attendance = Attendance::where('student_id', $dailyAttendance->student_id)
            ->whereDate('date', $date)
            ->first();

        if ($attendance && $attendance->source === 'manual') {
            return $attendance;
        }

        $approvedRequest = StudentEarlyLeaveRequest::where('student_id', $dailyAttendance->student_id)
            ->where('status', 'approved')
            ->whereDate('date', '<=', $date)
            ->whereRaw('COALESCE(date_end, date) >= ?', [$date])
            ->latest()
            ->first();

        $isOverdue = false;
        if (! $dailyAttendance->check_in_at && ! $approvedRequest) {
            $institutionId = $dailyAttendance->institution_id ?: $dailyAttendance->student?->institution_id;
            $setting = DailyAttendanceSetting::forInstitution($institutionId);
            $verificationDeadline = Carbon::parse($date.' '.$setting->check_in_verification_deadline);
            $isOverdue = now()->greaterThan($verificationDeadline);
        }

        $status = $this->resolver->deriveStatus($dailyAttendance, $approvedRequest, $isOverdue, $date);
        $finalStatus = $status === 'not_yet' ? 'absent' : $status;

        $attendance ??= new Attendance([
            'student_id' => $dailyAttendance->student_id,
            'date' => $date,
        ]);

        $attendance->fill([
            'class_id' => $dailyAttendance->class_id ?: $dailyAttendance->student?->class_id,
            'status' => $finalStatus,
            'check_in_time' => in_array($finalStatus, ['present', 'late'], true) && $dailyAttendance->check_in_at
                ? Carbon::parse($dailyAttendance->check_in_at)->format('H:i:s')
                : null,
            'note' => $this->noteFor($dailyAttendance),
            'institution_id' => $dailyAttendance->institution_id ?: $dailyAttendance->student?->institution_id,
            'source' => 'digital',
        ])->save();

        return $attendance;
    }

    private function noteFor(StudentDailyAttendance $dailyAttendance): ?string
    {
        if ($dailyAttendance->verification_note) {
            return $dailyAttendance->verification_note;
        }

        if ($dailyAttendance->check_in_suspicious_reason) {
            return $dailyAttendance->check_in_suspicious_reason;
        }

        return null;
    }
}
