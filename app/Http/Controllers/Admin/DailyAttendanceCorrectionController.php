<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentDailyAttendanceCorrection;
use App\Services\AuditLogger;
use App\Services\AttendanceCorrectionApplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyAttendanceCorrectionController extends Controller
{
    public function index()
    {
        $corrections = StudentDailyAttendanceCorrection::where('institution_id', Auth::user()->institution_id)
            ->with(['student', 'attendance.class', 'reviewer'])
            ->latest()
            ->paginate(25);

        return view('admin.daily-attendance.corrections', compact('corrections'));
    }

    public function review(Request $request, StudentDailyAttendanceCorrection $correction)
    {
        abort_unless((int) $correction->institution_id === (int) Auth::user()->institution_id, 403);

        $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'reviewer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($correction->status !== 'pending') {
            return redirect()->back()->with('error', 'Koreksi ini sudah diproses.');
        }

        if ($request->decision === 'reject' && !$request->filled('reviewer_note')) {
            return redirect()->back()->with('error', 'Alasan penolakan koreksi wajib diisi.');
        }

        $decision = $request->decision === 'approve' ? 'approved' : 'rejected';

        $attendance = $correction->attendance;

        $oldValues = [
            'target_event' => $correction->target_event,
            'original_status' => $correction->target_event === 'check_in' ? $attendance->check_in_status : $attendance->check_out_status,
            'original_time' => $correction->target_event === 'check_in' ? $attendance->check_in_at?->toIso8601String() : $attendance->check_out_at?->toIso8601String(),
        ];

        if ($decision === 'approved') {
            app(AttendanceCorrectionApplier::class)->apply($correction);
        }

        $correction->update([
            'status' => $decision,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'reviewer_note' => $request->input('reviewer_note'),
        ]);

        AuditLogger::log(
            'daily_attendance_correction_' . $request->decision,
            'student_daily_attendance_corrections',
            $correction->id,
            $correction->institution_id,
            $oldValues,
            [
                'decision' => $request->decision,
                'requested_status' => $correction->requested_status,
                'reason' => $correction->reason,
                'reviewer_note' => $request->input('reviewer_note'),
                'original_attendance_preserved' => $decision !== 'approved',
            ]
        );

        return redirect()->back()->with('success', 'Koreksi absensi berhasil diproses.');
    }
}
