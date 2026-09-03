<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agenda;
use App\Models\Schedule;
use App\Services\AttendanceSummaryService;
use App\Traits\ResolvesWaliKelasContext;

class DashboardController extends Controller
{
    use ResolvesWaliKelasContext;

    public function index(Request $request)
    {
        $context = $this->waliKelasContext($request);
        $class = $context['selectedClass'];
        $academicYearId = $context['selectedAcademicYearId'];

        if (!$class) {
            return view('walikelas.dashboard', array_merge($context, [
                'has_class' => false,
                'class_name' => null,
                'total_students' => 0,
                'today_attendance' => null,
                'weeklyTrend' => collect(),
                'subjectAgendas' => collect(),
            ]));
        }

        $studentIds = $this->waliKelasStudentsQuery($class->id, $academicYearId, $context['isActiveWaliContext'])->pluck('id')->all();
        $total_students = count($studentIds);
        $summaryService = app(AttendanceSummaryService::class);

        $todayStatuses = $summaryService->resolveForDate($studentIds, now()->toDateString(), $academicYearId ? [$academicYearId] : null);
        $todayStatusCollection = collect($todayStatuses);
        $presentCount = $todayStatusCollection->filter(fn ($status) => $status === 'present')->count();
        $lateCount = $todayStatusCollection->filter(fn ($status) => $status === 'late')->count();
        $excusedCount = $todayStatusCollection->filter(fn ($status) => $status === 'excused')->count();
        $sickCount = $todayStatusCollection->filter(fn ($status) => $status === 'sick')->count();
        $absentCount = $todayStatusCollection->filter(fn ($status) => $status === 'absent')->count();
        $totalPresent = $presentCount + $lateCount;

        $attendanceRate = $total_students > 0 ? round(($totalPresent / $total_students) * 100, 1) : 0;

        // Latest agendas
        $latest_agendas = Agenda::withoutGlobalScope('academic_year')
            ->where('class_id', $class->id)
            ->when($academicYearId, fn ($query) => $query->where('academic_year_id', $academicYearId))
            ->where('status', 'published')
            ->with('teacher', 'subject')
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        // This week's agenda count
        $weekAgendaCount = Agenda::withoutGlobalScope('academic_year')
            ->where('class_id', $class->id)
            ->when($academicYearId, fn ($query) => $query->where('academic_year_id', $academicYearId))
            ->where('status', 'published')
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        // This week's schedule count
        $weekScheduleCount = Schedule::withoutGlobalScope('academic_year')
            ->where('class_id', $class->id)
            ->when($academicYearId, fn ($query) => $query->where('academic_year_id', $academicYearId))
            ->whereIn('day', $this->getElapsedDays())
            ->count();

        // Weekly attendance trend (current week: Monday - Sunday)
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $weeklyResolved = $summaryService->resolveForRange(
            $studentIds,
            $weekStart->toDateString(),
            $weekEnd->toDateString(),
            $academicYearId ? [$academicYearId] : null
        );
        $weeklyTrend = collect();
        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $dateKey = $date->toDateString();
            $dayStatuses = collect($weeklyResolved)
                ->map(fn (array $statuses) => $statuses[$dateKey] ?? null)
                ->filter(fn ($status) => $status && $status !== 'not_yet');
            $dayTotal = $dayStatuses->count();
            $dayPresent = $dayStatuses->filter(fn ($status) => in_array($status, ['present', 'late'], true))->count();

            $weeklyTrend->push([
                'day' => $date->translatedFormat('D'),
                'date' => $date->format('d/m'),
                'rate' => $total_students > 0 ? round(($dayPresent / $total_students) * 100) : 0,
                'total' => $dayTotal,
            ]);
        }

        // Subject distribution (agendas per subject this month)
        $subjectAgendas = Agenda::withoutGlobalScope('academic_year')
            ->where('class_id', $class->id)
            ->when($academicYearId, fn ($query) => $query->where('academic_year_id', $academicYearId))
            ->where('status', 'published')
            ->where('date', '>=', now()->startOfMonth())
            ->join('subjects', 'agendas.subject_id', '=', 'subjects.id')
            ->select('subjects.name', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('subjects.name')
            ->pluck('total', 'name')
            ->sortDesc()
            ->take(5);

        return view('walikelas.dashboard', array_merge($context, [
            'has_class' => true,
            'class' => $class,
            'total_students' => $total_students,
            'today_attendance' => $todayStatuses,
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
            'excusedCount' => $excusedCount,
            'sickCount' => $sickCount,
            'absentCount' => $absentCount,
            'totalPresent' => $presentCount + $lateCount,
            'attendanceRate' => $attendanceRate,
            'latest_agendas' => $latest_agendas,
            'weekAgendaCount' => $weekAgendaCount,
            'weekScheduleCount' => $weekScheduleCount,
            'weeklyTrend' => $weeklyTrend,
            'subjectAgendas' => $subjectAgendas,
        ]));
    }

    private function getElapsedDays()
    {
        $startOfWeek = now()->startOfWeek();
        $today = now();
        $days = [];
        $daysElapsed = $startOfWeek->diffInDays($today) + 1;

        for ($i = 0; $i < $daysElapsed; $i++) {
            $days[] = $startOfWeek->copy()->addDays($i)->format('l');
        }

        return $days;
    }
}
