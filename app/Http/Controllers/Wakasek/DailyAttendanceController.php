<?php

namespace App\Http\Controllers\Wakasek;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\StudentDailyAttendance;
use App\Models\StudentEarlyLeaveRequest;
use Illuminate\Http\Request;

class DailyAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $date = $request->query('date', now()->toDateString());
        $selectedClassId = $request->query('class_id');

        $classes = Classes::query()
            ->with(['students', 'homeroomTeacher'])
            ->when($user->institution_id, fn ($query) => $query->where('institution_id', $user->institution_id))
            ->orderBy('name')
            ->get();

        if ($selectedClassId && ! $classes->contains('id', (int) $selectedClassId)) {
            $selectedClassId = null;
        }

        $visibleClasses = $selectedClassId
            ? $classes->where('id', (int) $selectedClassId)->values()
            : $classes;

        $records = StudentDailyAttendance::withoutGlobalScope('academic_year')
            ->whereIn('class_id', $visibleClasses->pluck('id'))
            ->whereDate('date', $date)
            ->with(['student', 'class', 'whatsappLogs', 'corrections.reviewer', 'earlyLeaveRequests.reviewer'])
            ->orderBy('class_id')
            ->orderByRaw('check_in_at IS NULL, check_in_at ASC')
            ->get()
            ->keyBy('student_id');

        $earlyLeaveRequestsByStudent = StudentEarlyLeaveRequest::whereIn('class_id', $visibleClasses->pluck('id'))
            ->where('date', '<=', $date)
            ->whereRaw('COALESCE(date_end, date) >= ?', [$date])
            ->with('reviewer')
            ->get()
            ->groupBy('student_id');

        $records = $visibleClasses->flatMap(function ($class) use ($records, $earlyLeaveRequestsByStudent, $date) {
            return $class->students->map(function ($student) use ($records, $earlyLeaveRequestsByStudent, $class, $date) {
                $record = $records->get($student->id);

                if ($record) {
                    $record->setRelation('earlyLeaveRequests', $earlyLeaveRequestsByStudent->get($student->id, collect())->values());

                    return $record;
                }

                $record = new StudentDailyAttendance([
                    'student_id' => $student->id,
                    'class_id' => $class->id,
                    'date' => $date,
                ]);
                $record->setRelation('student', $student);
                $record->setRelation('class', $class);
                $record->setRelation('whatsappLogs', collect());
                $record->setRelation('corrections', collect());
                $record->setRelation('earlyLeaveRequests', $earlyLeaveRequestsByStudent->get($student->id, collect())->values());

                return $record;
            });
        })->values();

        $classStats = $visibleClasses->map(function ($class) use ($records) {
            $classRecords = $records->where('class_id', $class->id)->values();

            $total = $class->students->count();
            $checkedIn = $classRecords->whereNotNull('check_in_at')->count();
            $checkedOut = $classRecords->whereNotNull('check_out_at')->count();
            $late = $classRecords->filter(fn ($record) => (int) $record->late_minutes > 0)->count();
            $needsVerification = $classRecords->filter(
                fn ($record) => $record->check_in_status === 'alpha' || $record->check_out_status === 'alpha'
            )->count();

            return (object) [
                'class' => $class,
                'total' => $total,
                'checked_in' => $checkedIn,
                'checked_out' => $checkedOut,
                'late' => $late,
                'needs_verification' => $needsVerification,
                'not_checked_in' => max(0, $total - $checkedIn),
                'attendance_rate' => $total > 0 ? round(($checkedIn / $total) * 100) : 0,
                'checkout_rate' => $total > 0 ? round(($checkedOut / $total) * 100) : 0,
            ];
        });

        return view('wakasek.daily-attendance.index', [
            'date' => $date,
            'records' => $records,
            'class' => $selectedClassId ? $classes->firstWhere('id', (int) $selectedClassId) : null,
            'classes' => $classes,
            'selectedClassId' => $selectedClassId,
            'classStats' => $classStats,
            'emptyText' => 'Belum ada data absensi harian pada tanggal ini.',
        ]);
    }
}
