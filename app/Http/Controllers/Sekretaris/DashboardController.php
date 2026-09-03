<?php
// app/Http/Controllers/Sekretaris/DashboardController.php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Classes;
use App\Models\User;
use App\Services\AttendanceSummaryService;
use App\Traits\ResolvesSekretarisClassContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use ResolvesSekretarisClassContext;

    public function index(Request $request)
    {
        $context = $this->sekretarisClassContext($request);
        $classId = $context['selectedClassId'];
        
        if (!$classId) {
            $stats = [
                'total_agendas' => 0,
                'avg_attendance' => 0,
                'total_classes' => 0,
                'total_students' => 0,
            ];
            $today_attendance = [];
            $recent_agendas = collect();
            $monthly_attendance = collect([]);
            return view('sekretaris.dashboard', array_merge(
                compact('stats', 'today_attendance', 'recent_agendas', 'monthly_attendance'),
                $context
            ));
        }

        $studentIds = $this->sekretarisStudentQuery($classId, $context['selectedAcademicYearId'], $context['isActivePeriod'])->pluck('id')->all();
        $studentCount = count($studentIds);

        $service = app(AttendanceSummaryService::class);

        // Stats filtered by class
        $stats = [
            'total_agendas' => Agenda::withoutGlobalScope('academic_year')->where('class_id', $classId)->where('status', 'published')->whereMonth('date', date('m'))->count(),
            'avg_attendance' => $this->getAverageAttendance($studentIds, $service),
            'total_classes' => 1, // Only their class
            'total_students' => $studentCount,
        ];
        
        // Today's attendance for THEIR class
        $today_attendance = [];
        $class = Classes::find($classId);
        
        if ($class) {
            $todayResolved = $service->resolveForDate($studentIds, now()->toDateString());
            $present = collect($todayResolved)->filter(fn ($status) => in_array($status, ['present', 'late'], true))->count();
            
            $today_attendance[] = [
                'class_name' => $class->name,
                'present' => $present,
                'total' => $studentCount
            ];
        }
        
        // Recent agendas for THEIR class
        $recent_agendas = Agenda::withoutGlobalScope('academic_year')
            ->where('class_id', $classId)
            ->where('status', 'published')
            ->with(['class', 'teacher'])
            ->latest()
            ->take(5)
            ->get();
        
        // Monthly Attendance for Chart - resolved from manual + digital
        $monthly_attendance = collect();
        $startOfRange = \Carbon\Carbon::now()->subMonths(5)->startOfMonth();
        $resolved = $service->resolveForRange($studentIds, $startOfRange->toDateString(), now()->toDateString());

        $monthMap = [];
        foreach ($resolved as $statuses) {
            foreach ($statuses as $date => $status) {
                $monthKey = substr($date, 0, 7);
                $monthMap[$monthKey][$status] = ($monthMap[$monthKey][$status] ?? 0) + 1;
            }
        }

        for ($i = 5; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subMonths($i);
            $monthKey = $date->format('Y-m');

            $monthStats = $monthMap[$monthKey] ?? [];

            $present = (int)($monthStats['present'] ?? 0);
            $late = (int)($monthStats['late'] ?? 0);
            $excused = (int)($monthStats['excused'] ?? 0);
            $absent = (int)($monthStats['absent'] ?? 0);
            $sick = (int)($monthStats['sick'] ?? 0);
            $totalCount = $present + $late + $excused + $absent + $sick;

            $monthly_attendance->push([
                'month_key' => $monthKey,
                'month' => $date->translatedFormat('F'),
                'present' => $present,
                'late' => $late,
                'excused' => $excused,
                'absent' => $absent,
                'sick' => $sick,
                'percentage' => $totalCount > 0 ? round((($present + $late) / $totalCount) * 100, 1) : 0,
            ]);
        }
        
        return view('sekretaris.dashboard', array_merge(
            compact('stats', 'today_attendance', 'recent_agendas', 'monthly_attendance'),
            $context
        ));
    }
    
    private function getAverageAttendance(array $studentIds, AttendanceSummaryService $service): float|int
    {
        if (empty($studentIds)) return 0;

        $start = now()->startOfMonth()->toDateString();
        $end = now()->toDateString();

        $resolved = $service->resolveForRange($studentIds, $start, $end);
        $counts = $service->countStatuses($resolved);

        if ($counts['total'] == 0) return 0;

        return round((($counts['present'] + $counts['late']) / $counts['total']) * 100, 1);
    }
}
