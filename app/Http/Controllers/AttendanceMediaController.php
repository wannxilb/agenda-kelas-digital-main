<?php

namespace App\Http\Controllers;

use App\Models\StudentDailyAttendance;
use App\Models\StudentDailyAttendanceCorrection;
use App\Models\StudentEarlyLeaveRequest;
use App\Models\AuditLog;
use App\Models\Schedule;
use App\Models\User;
use App\Services\PrivateAttendanceMedia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttendanceMediaController extends Controller
{
    public function attendance(Request $request, StudentDailyAttendance $attendance, string $type, PrivateAttendanceMedia $media)
    {
        abort_unless(in_array($type, ['check-in', 'check-out'], true), 404);
        abort_unless($this->canViewAttendance($attendance), 403);

        $path = $type === 'check-in' ? $attendance->check_in_photo : $attendance->check_out_photo;
        abort_unless($path, 404);

        $this->auditMediaAccess('daily_attendance_media_viewed', 'student_daily_attendances', $attendance->id, $attendance->institution_id, ['type' => $type]);

        return response($media->read($path), 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="attendance-' . $attendance->id . '-' . $type . '.jpg"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function correction(Request $request, StudentDailyAttendanceCorrection $correction, PrivateAttendanceMedia $media)
    {
        abort_unless($correction->evidence_path, 404);
        $attendance = $correction->attendance()->firstOrFail();
        abort_unless($this->canViewAttendance($attendance), 403);

        $this->auditMediaAccess('daily_attendance_correction_evidence_viewed', 'student_daily_attendance_corrections', $correction->id, $attendance->institution_id);

        return response($media->read($correction->evidence_path), 200, [
            'Content-Type' => $correction->evidence_mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="correction-' . $correction->id . '"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function earlyLeave(StudentEarlyLeaveRequest $earlyLeaveRequest, PrivateAttendanceMedia $media)
    {
        abort_unless($earlyLeaveRequest->evidence_path, 404);
        $attendance = new StudentDailyAttendance(['student_id' => $earlyLeaveRequest->student_id, 'class_id' => $earlyLeaveRequest->class_id, 'institution_id' => $earlyLeaveRequest->institution_id]);
        abort_unless($this->canViewAttendance($attendance), 403);
        $this->auditMediaAccess('early_leave_evidence_viewed', 'student_early_leave_requests', $earlyLeaveRequest->id, $earlyLeaveRequest->institution_id);

        return response($media->read($earlyLeaveRequest->evidence_path), 200, [
            'Content-Type' => $earlyLeaveRequest->evidence_mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="early-leave-' . $earlyLeaveRequest->id . '"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function auditMediaAccess(string $action, string $table, int $recordId, ?int $institutionId, ?array $newValues = null): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'institution_id' => $institutionId,
            'action' => $action,
            'table_name' => $table,
            'record_id' => $recordId,
            'old_values' => null,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'browser' => request()->userAgent(),
        ]);
    }

    private function canViewAttendance(StudentDailyAttendance $attendance): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user || (int) $user->institution_id !== (int) $attendance->institution_id) {
            return false;
        }

        if (
            $user->hasRole('super_admin')
            || $user->hasRole('admin')
            || $user->hasRole('sekretaris')
            || $user->hasRole('secretary')
            || $user->hasRole('wakasek')
        ) {
            return true;
        }

        if ($user->hasRole('siswa')) {
            return (int) $attendance->student_id === (int) $user->id;
        }

        if ($user->hasRole('wali_kelas')) {
            return $user->classes()->whereKey($attendance->class_id)->exists();
        }

        if ($user->hasRole('teacher')) {
            return $this->isFirstOrLastScheduleTeacher($user, $attendance);
        }

        return false;
    }

    private function isFirstOrLastScheduleTeacher(User $user, StudentDailyAttendance $attendance): bool
    {
        if (!$attendance->date || !$attendance->class_id) {
            return false;
        }

        $dayName = Carbon::parse($attendance->date)->format('l');
        $schedules = Schedule::where('class_id', $attendance->class_id)
            ->where('day', $dayName)
            ->orderBy('start_time')
            ->get();

        if ($schedules->isEmpty()) {
            return false;
        }

        $first = $schedules->first();
        $last = $schedules->sortByDesc('end_time')->first();

        return (int) $first?->teacher_id === (int) $user->id
            || (int) $last?->teacher_id === (int) $user->id;
    }
}
