<?php

// app/Http/Controllers/Guru/DashboardController.php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Attendance;
use App\Models\User;
use App\Services\TeacherStatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private TeacherStatusService $teacherStatusService) {}

    public function index()
    {
        /** @var User $teacher */
        $teacher = Auth::user();
        $today = now()->format('l');

        // Today's Teaching Schedule
        $todaySchedules = $teacher->teachingSchedules()
            ->where('day', $today)
            ->with(['class', 'subject'])
            ->orderBy('start_time')
            ->get();

        // Homeroom Class Statistics
        $homeroomClass = $teacher->classHomeroom()->withCount('students')->first();
        $todayAttendance = null;
        if ($homeroomClass) {
            $todayAttendance = Attendance::where('class_id', $homeroomClass->id)
                ->where('date', date('Y-m-d'))
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->get();
        }

        // Statistics
        $totalAgendas = Agenda::where('teacher_id', $teacher->id)->where('status', 'published')->count();
        $totalClasses = $teacher->teachingSchedules()->distinct('class_id')->count('class_id');
        $totalSubjects = $teacher->teachingSchedules()->distinct('subject_id')->count('subject_id');
        $totalStudents = $homeroomClass ? $homeroomClass->students_count : 0;

        // Recent Activity
        $recentAgendas = Agenda::where('teacher_id', $teacher->id)
            ->where('status', 'published')
            ->with(['class', 'subject'])
            ->latest()
            ->take(5)
            ->get();

        $driver = DB::connection()->getDriverName();
        $monthSelect = $driver === 'sqlite' ? 'strftime("%Y-%m", date)' : ($driver === 'pgsql' ? "to_char(date, 'YYYY-MM')" : "DATE_FORMAT(date, '%Y-%m')");

        // Monthly Stats for Chart (fill missing months with 0)
        $rawStats = Agenda::where('teacher_id', $teacher->id)
            ->where('status', 'published')
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->select(
                DB::raw($monthSelect.' as month'),
                DB::raw('count(*) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthlyStats = collect([]);
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $monthlyStats->push((object) [
                'month' => $key,
                'total' => (int) ($rawStats[$key]->total ?? 0),
            ]);
        }

        // Rekap bulanan izin/tugas luar milik guru (approved) + jumlah pending
        $teacherStatusSummary = $this->teacherStatusService->teacherMonthlySummary($teacher);

        return view('guru.dashboard', [
            'teacher' => $teacher,
            'todaySchedules' => $todaySchedules,
            'homeroomClass' => $homeroomClass,
            'todayAttendance' => $todayAttendance,
            'totalAgendas' => $totalAgendas,
            'totalClasses' => $totalClasses,
            'totalSubjects' => $totalSubjects,
            'totalStudents' => $totalStudents,
            'recentAgendas' => $recentAgendas,
            'monthlyStats' => $monthlyStats,
            'teacherStatusSummary' => $teacherStatusSummary,
        ]);
    }
}
