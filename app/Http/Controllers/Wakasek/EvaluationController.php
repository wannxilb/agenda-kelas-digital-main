<?php

namespace App\Http\Controllers\Wakasek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Classes;
use App\Models\Agenda;
use App\Models\Attendance;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EvaluationController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('view', User::class);

        $institutionId = auth()->user()?->institution_id;

        // 1. General Stats
        $totalTeachers = User::role('teacher')->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))->count();
        $totalStudents = User::role('siswa')->where('status', 'active')->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))->count();
        $totalJournals = Agenda::where('status', 'published')->count();
        $todayJournals = Agenda::where('status', 'published')->whereDate('date', today())->count();

        // 2. Attendance Summary by Class (Percentage)
        $classes = Classes::query()
            ->withCount('students')
            ->withCount(['attendances' => function ($q) {
                $q->whereIn('status', ['present', 'late']);
            }])
            ->get();

        // Get distinct attendance dates per class (single query instead of N+1)
        $distinctDatesPerClass = DB::table('attendances')
            ->join('users', 'attendances.student_id', '=', 'users.id')
            ->when($institutionId, fn ($q) => $q->where('attendances.institution_id', $institutionId))
            ->select('users.class_id', DB::raw('count(distinct attendances.date) as distinct_dates'))
            ->groupBy('users.class_id')
            ->pluck('distinct_dates', 'class_id');

        $classAttendance = $classes->map(function ($class) use ($distinctDatesPerClass) {
            $distinctDatesCount = $distinctDatesPerClass->get($class->id, 0);
            $totalExpected = ($class->students_count ?? 0) * $distinctDatesCount;
            $presentCount = $class->attendances_count;

            return [
                'id' => $class->id,
                'name' => $class->name,
                'percentage' => $totalExpected > 0 ? round(($presentCount / $totalExpected) * 100, 1) : 0,
                'present' => $presentCount,
                'student_count' => $class->students_count ?? 0,
            ];
        })->sortByDesc('percentage');

        $avgAttendance = $classAttendance->count() > 0
            ? round($classAttendance->avg('percentage'), 1)
            : 0;

        // 3. Absence Trends (Current Month)
        $absenceStats = Attendance::where('date', '>=', now()->startOfMonth())
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');

        $totalAbsences = $absenceStats->sum();

        // 4. Teaching Compliance (Journal vs Schedule)
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $daysElapsed = $startOfWeek->diffInDays($today) + 1;

        $dayNames = [];
        for ($i = 0; $i < $daysElapsed; $i++) {
            $dayNames[] = $startOfWeek->copy()->addDays($i)->format('l');
        }

        $expectedPerWeek = Schedule::whereIn('day', $dayNames)->count();
        $actualThisWeek = Agenda::where('status', 'published')->whereBetween('date', [$startOfWeek, $today])->count();
        $teachingCompliance = $expectedPerWeek > 0 ? round(($actualThisWeek / $expectedPerWeek) * 100, 1) : 0;

        // 5. Top/Bottom Classes by Agenda Count
        $classAgendaCounts = Agenda::where('status', 'published')
            ->select('class_id', DB::raw('count(*) as total'))
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $topClasses = Classes::whereIn('id', $classAgendaCounts->keys())
            ->get()
            ->map(function ($class) use ($classAgendaCounts) {
                return [
                    'name' => $class->name,
                    'count' => $classAgendaCounts[$class->id] ?? 0,
                ];
            })
            ->sortByDesc('count')
            ->take(5);

        // 6. Teacher Performance
        $teacherPerformance = User::role('teacher')
            ->withCount(['agendas' => function ($q) {
                $q->where('status', 'published');
            }])
            ->with(['teachingSchedules', 'subjects'])
            ->get()
            ->map(function ($teacher) {
                $scheduleCount = $teacher->teachingSchedules->count();
                $agendaCount = $teacher->agendas_count;
                $compliance = $scheduleCount > 0 ? min(100, round(($agendaCount / $scheduleCount) * 100)) : 0;
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'nip' => $teacher->nip,
                    'agenda_count' => $agendaCount,
                    'schedule_count' => $scheduleCount,
                    'compliance' => $compliance,
                    'subjects' => $teacher->subjects->pluck('name')->take(2)->implode(', '),
                ];
            })
            ->sortBy([
                ['compliance', 'desc'],
                ['name', 'asc'],
            ])
            ->values();

        $activeTeachers = $teacherPerformance->filter(fn($t) => $t['agenda_count'] > 0)->count();
        $inactiveTeachers = $totalTeachers - $activeTeachers;
        $avgCompliance = $teacherPerformance->count() > 0 ? round($teacherPerformance->avg('compliance'), 1) : 0;

        return view('wakasek.evaluation.index', compact(
            'totalTeachers',
            'totalStudents',
            'totalJournals',
            'todayJournals',
            'classAttendance',
            'avgAttendance',
            'absenceStats',
            'totalAbsences',
            'teachingCompliance',
            'topClasses',
            'expectedPerWeek',
            'actualThisWeek',
            'teacherPerformance',
            'activeTeachers',
            'inactiveTeachers',
            'avgCompliance'
        ));
    }

    public function report()
    {
        $this->authorize('view', User::class);

        $institutionId = auth()->user()?->institution_id;
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();

        $classes = Classes::withCount(['agendas' => function ($q) {
            $q->where('status', 'published');
        }])
            ->withCount(['attendances' => function ($q) {
                $q->whereIn('status', ['present', 'late']);
            }])->get();

        $studentUserIds = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'siswa')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('model_has_roles.model_id');

        $studentsPerClass = DB::table('users')
            ->whereIn('id', $studentUserIds)
            ->where('status', '!=', 'graduated')
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->select('class_id', DB::raw('count(*) as total'))
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $distinctDatesPerClass = DB::table('attendances')
            ->join('users', 'attendances.student_id', '=', 'users.id')
            ->when($institutionId, fn ($q) => $q->where('attendances.institution_id', $institutionId))
            ->select('users.class_id', DB::raw('count(distinct attendances.date) as distinct_dates'))
            ->groupBy('users.class_id')
            ->pluck('distinct_dates', 'class_id');

        $classReport = $classes->map(function ($class) use ($studentsPerClass, $distinctDatesPerClass) {
            $studentCount = $studentsPerClass->get($class->id, 0);
            $distinctDatesCount = $distinctDatesPerClass->get($class->id, 0);
            $totalExpected = $studentCount * $distinctDatesCount;
            $presentCount = $class->attendances_count;

            return [
                'id' => $class->id,
                'name' => $class->name,
                'student_count' => $studentCount,
                'attendance_percentage' => $totalExpected > 0 ? round(($presentCount / $totalExpected) * 100, 1) : 0,
                'agenda_count' => $class->agendas_count,
            ];
        });

        $totalStudents = $classReport->sum('student_count');
        $totalAgendas = $classReport->sum('agenda_count');
        $avgAttendance = $classReport->count() > 0 ? round($classReport->avg('attendance_percentage'), 1) : 0;

        return view('wakasek.evaluation.report', compact(
            'classReport',
            'activeYear',
            'totalStudents',
            'totalAgendas',
            'avgAttendance'
        ));
    }
}
