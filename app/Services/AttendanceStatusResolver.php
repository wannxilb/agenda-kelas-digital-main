<?php

namespace App\Services;

use App\Models\DailyAttendanceSetting;
use App\Models\Setting;
use App\Models\StudentDailyAttendance;
use App\Models\StudentEarlyLeaveRequest;
use App\Models\User;
use Carbon\Carbon;

class AttendanceStatusResolver
{
    public function resolve(int $studentId, string $date): string
    {
        $attendance = StudentDailyAttendance::where('student_id', $studentId)
            ->where('date', $date)
            ->first();

        $approvedRequest = StudentEarlyLeaveRequest::where('student_id', $studentId)
            ->where('status', 'approved')
            ->where('date', '<=', $date)
            ->whereRaw('COALESCE(date_end, date) >= ?', [$date])
            ->latest()
            ->first();

        $student = User::find($studentId);
        if (! $student) {
            return 'absent';
        }
        $setting = DailyAttendanceSetting::forInstitution($student->institution_id);

        $verificationDeadline = Carbon::parse($date.' '.$setting->check_in_verification_deadline);
        $isOverdue = now()->greaterThan($verificationDeadline);

        return $this->deriveStatus($attendance, $approvedRequest, $isOverdue, $date);
    }

    public function deriveStatus(
        ?StudentDailyAttendance $attendance,
        ?StudentEarlyLeaveRequest $approvedRequest,
        bool $isOverdue = false,
        ?string $date = null
    ): string {
        if ($this->isTidakHadir($approvedRequest)) {
            return $approvedRequest->category === 'sakit'
                ? 'sick'
                : 'excused';
        }

        if ($this->isDispen($approvedRequest)) {
            if ($attendance && $attendance->check_in_at) {
                return 'present';
            }

            return $this->dispenWithoutCheckInStatus($approvedRequest, $date);
        }

        if ($attendance && $attendance->check_in_at) {
            return $this->deriveCheckInStatus($attendance);
        }

        return $isOverdue ? 'absent' : 'not_yet';
    }

    private function dispenWithoutCheckInStatus(StudentEarlyLeaveRequest $request, ?string $date): string
    {
        $today = now()->toDateString();
        if ($date === null || $date > $today) {
            return 'present';
        }

        $operationalEnd = Setting::get('operational_end_time', '16:00', $request->institution_id);
        $endOfDay = Carbon::parse($date.' '.$operationalEnd);

        return now()->greaterThan($endOfDay) ? 'unknown' : 'present';
    }

    private function isTidakHadir(?StudentEarlyLeaveRequest $request): bool
    {
        return $request && $request->isTidakHadirCategory();
    }

    private function isDispen(?StudentEarlyLeaveRequest $request): bool
    {
        return $request && $request->isDispenCategory();
    }

    private function deriveCheckInStatus(StudentDailyAttendance $attendance): string
    {
        if ($attendance->check_in_status === 'alpha' || $attendance->check_in_status === 'teacher_rejected') {
            return 'absent';
        }

        if (in_array($attendance->check_in_status, ['sick', 'excused', 'additional_activity'], true)) {
            return $attendance->check_in_status === 'additional_activity' ? 'present' : $attendance->check_in_status;
        }

        if ($attendance->check_in_status === 'late'
            || ($attendance->check_in_status === 'teacher_verified' && (int) $attendance->late_minutes > 0)
            || ($attendance->check_in_status === 'needs_verification' && (int) $attendance->late_minutes > 0)) {
            return 'late';
        }

        return 'present';
    }
}
