<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\TeacherStatus;
use App\Services\TeacherStatusService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private TeacherStatusService $teacherStatusService) {}

    public function index()
    {
        $user = Auth::user();
        $classId = $user->class_id;

        if (! $classId) {
            return view('siswa.dashboard', [
                'user' => $user,
                'attendance_stats' => [
                    'total' => 0, 'present' => 0, 'late' => 0,
                    'excused' => 0, 'absent' => 0, 'sick' => 0, 'percentage' => 0,
                ],
                'recent_agendas' => collect(),
                'today_schedules' => collect(),
                'monthly_attendance' => collect(),
                'noClass' => true,
            ]);
        }

        $stats = Attendance::where('student_id', $user->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $total = $stats->sum();
        $present = $stats->get('present', 0);
        $late = $stats->get('late', 0);

        $attendance_stats = [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'excused' => $stats->get('excused', 0),
            'absent' => $stats->get('absent', 0),
            'sick' => $stats->get('sick', 0),
        ];

        $attendance_stats['percentage'] = $total > 0
            ? round((($present + $late) / $total) * 100)
            : 0;

        $recent_agendas = Agenda::where('class_id', $classId)
            ->where('status', 'published')
            ->with(['teacher', 'subject'])
            ->latest()
            ->take(5)
            ->get();

        $dayOfWeek = Carbon::now()->locale('en')->format('l');
        $today_schedules = Schedule::where('class_id', $classId)
            ->where('day', $dayOfWeek)
            ->whereIn('week_type', \App\Models\Setting::scheduleWeekTypesForDate(Carbon::today()))
            ->with(['teacher', 'subject'])
            ->orderBy('start_time')
            ->get();

        $today = Carbon::today()->toDateString();
        $teacherStatuses = $this->teacherStatusService->approvedForDateRange(
            $today,
            $today,
            $today_schedules->pluck('teacher_id')->filter()->unique()->values()->all()
        );
        $this->annotateSchedulesForDate($today_schedules, $teacherStatuses, $today);

        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        // Ekspresi bulan-tahun yang kompatibel lintas driver (pola sama dengan
        // Guru/Admin Dashboard): sqlite → strftime (DB test E2E), pgsql →
        // to_char, mysql → DATE_FORMAT.
        $driver = DB::connection()->getDriverName();
        $monthExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', date)"
            : ($driver === 'pgsql' ? "to_char(date, 'YYYY-MM')" : "DATE_FORMAT(date, '%Y-%m')");
        $monthlyStats = Attendance::where('student_id', $user->id)
            ->where('date', '>=', $sixMonthsAgo)
            ->selectRaw("{$monthExpr} as year_month, status, count(*) as count")
            ->groupBy('year_month', 'status')
            ->get()
            ->groupBy('year_month');

        $monthly_attendance = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $key = $month->format('Y-m');
            $monthData = $monthlyStats->get($key, collect())->keyBy('status');

            $monthly_attendance->push([
                'month' => $month->translatedFormat('F'),
                'present' => $monthData->get('present', (object) ['count' => 0])->count ?? 0,
                'late' => $monthData->get('late', (object) ['count' => 0])->count ?? 0,
                'excused' => $monthData->get('excused', (object) ['count' => 0])->count ?? 0,
                'absent' => $monthData->get('absent', (object) ['count' => 0])->count ?? 0,
                'sick' => $monthData->get('sick', (object) ['count' => 0])->count ?? 0,
            ]);
        }

        return view('siswa.dashboard', compact(
            'user',
            'attendance_stats',
            'recent_agendas',
            'today_schedules',
            'monthly_attendance'
        ));
    }

    private function annotateSchedulesForDate(Collection $schedules, Collection $teacherStatuses, string $date): void
    {
        $statusesByTeacher = $teacherStatuses->groupBy('teacher_id');

        foreach ($schedules as $schedule) {
            $status = $statusesByTeacher
                ->get($schedule->teacher_id, collect())
                ->first(fn (TeacherStatus $teacherStatus) => $teacherStatus->coversDate($date));

            $schedule->setRelation('teacherStatusForStudent', $status);
        }
    }
}
