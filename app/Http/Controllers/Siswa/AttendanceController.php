<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\StudentDailyAttendance;
use App\Services\AttendanceSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month', date('Y-m'));
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();
        $summaryService = app(AttendanceSummaryService::class);
        $resolved = $summaryService->resolveForRange([$user->id], $startDate->toDateString(), $endDate->toDateString())[$user->id] ?? [];
        $counts = $summaryService->countPerStudent([$user->id => $resolved])[$user->id]
            ?? ['total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'sick' => 0, 'excused' => 0];

        $stats = [
            'total' => $counts['total'] ?? 0,
            'present' => $counts['present'] ?? 0,
            'absent' => $counts['absent'] ?? 0,
            'late' => $counts['late'] ?? 0,
            'sick' => $counts['sick'] ?? 0,
            'excused' => $counts['excused'] ?? 0,
        ];

        $stats['percentage'] = $stats['total'] > 0
            ? round((($stats['present'] + $stats['late']) / $stats['total']) * 100)
            : 0;

        $manualAttendances = Attendance::withoutGlobalScope('academic_year')
            ->where('student_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn (Attendance $attendance) => $attendance->date->toDateString());

        $digitalAttendances = StudentDailyAttendance::where('student_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn (StudentDailyAttendance $attendance) => $attendance->date->toDateString());

        $records = collect($resolved)
            ->reject(fn (string $status) => $status === 'not_yet')
            ->map(function (string $status, string $date) use ($manualAttendances, $digitalAttendances) {
                $manual = $manualAttendances->get($date);
                $digital = $digitalAttendances->get($date);

                return (object) [
                    'date' => Carbon::parse($date),
                    'status' => $status,
                    'check_in_time' => $digital?->check_in_at?->format('H:i:s') ?? $manual?->check_in_time,
                    'note' => $manual?->note,
                ];
            })
            ->sortByDesc(fn ($attendance) => $attendance->date->toDateString())
            ->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $attendances = new LengthAwarePaginator(
            $records->forPage($page, $perPage)->values(),
            $records->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $todayStatus = $summaryService->resolveForDate([$user->id], Carbon::today()->toDateString())[$user->id] ?? null;
        $todayDigital = StudentDailyAttendance::where('student_id', $user->id)
            ->whereDate('date', Carbon::today()->toDateString())
            ->first();
        $todayManual = Attendance::where('student_id', $user->id)
            ->whereDate('date', Carbon::today()->toDateString())
            ->first();
        $today_attendance = $todayStatus && $todayStatus !== 'not_yet'
            ? (object) [
                'date' => Carbon::today(),
                'status' => $todayStatus,
                'check_in_time' => $todayDigital?->check_in_at?->format('H:i:s') ?? $todayManual?->check_in_time,
            ]
            : null;

        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subMonths($i);
            $months[$date->format('Y-m')] = $date->translatedFormat('F Y');
        }

        return view('siswa.attendance.index', compact('stats', 'today_attendance', 'attendances', 'months'));
    }
}
