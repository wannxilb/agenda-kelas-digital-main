<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\AttendanceSummaryService;
use App\Traits\ResolvesWaliKelasContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExportController extends Controller
{
    use ResolvesWaliKelasContext;

    public function exportAttendance(Request $request)
    {
        $context = $this->waliKelasContext($request);
        $class = $context['selectedClass'];
        $academicYear = $context['selectedAcademicYear'];
        $academicYearId = $context['selectedAcademicYearId'];

        if (!$class) {
            return view('walikelas.export.attendance', array_merge($context, [
                'has_class' => false,
                'academicYear' => null,
                'totalStudents' => 0,
                'attendanceRate' => 0,
                'schoolDays' => 0,
                'totalAbsences' => 0,
                'monthlyData' => collect(),
                'months' => [],
            ]));
        }

        $months = [];
        for ($i = 0; $i < 6; $i++) {
            $date = date('Y-m', strtotime("-$i months"));
            $months[$date] = date('F Y', strtotime($date));
        }

        $studentIds = $this->waliKelasStudentsQuery($class->id, $academicYearId, $context['isActiveWaliContext'])->pluck('id');
        $totalStudents = $studentIds->count();

        $rangeStart = $academicYear?->start_date?->toDateString() ?? now()->subMonths(5)->startOfMonth()->toDateString();
        $rangeEnd = $academicYear?->end_date?->toDateString() ?? now()->toDateString();
        $resolved = app(AttendanceSummaryService::class)
            ->resolveForRange($studentIds->all(), $rangeStart, $rangeEnd, $academicYearId ? [$academicYearId] : null);
        $summary = app(AttendanceSummaryService::class)->countStatuses($resolved);

        $totalRecords = $summary['total'];
        $totalPresent = $summary['present'] + $summary['late'];
        $attendanceRate = $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100, 1) : 0;
        $schoolDays = collect($resolved)->flatMap(fn (array $statuses) => array_keys($statuses))->unique()->count();
        $totalAbsences = $summary['absent'];

        $monthlyData = collect($resolved)
            ->flatMap(fn (array $statuses) => collect($statuses)->map(fn (string $status, string $date) => compact('date', 'status')))
            ->groupBy(fn (array $item) => Carbon::parse($item['date'])->format('Y-m-01'))
            ->map(function ($items, string $month) {
                return (object) [
                    'date' => $month,
                    'total' => $items->count(),
                    'present' => $items->whereIn('status', ['present', 'late'])->count(),
                    'sick' => $items->where('status', 'sick')->count(),
                    'excused' => $items->where('status', 'excused')->count(),
                    'absent' => $items->where('status', 'absent')->count(),
                ];
            })
            ->sortByDesc('date')
            ->values();

        return view('walikelas.export.attendance', array_merge($context, [
            'has_class' => true,
            'class' => $class,
            'months' => $months,
            'academicYear' => $academicYear,
            'totalStudents' => $totalStudents,
            'attendanceRate' => $attendanceRate,
            'schoolDays' => $schoolDays,
            'totalAbsences' => $totalAbsences,
            'monthlyData' => $monthlyData,
        ]));
    }

    public function exportAttendancePDF(Request $request)
    {
        $context = $this->waliKelasContext($request);
        $class = $context['selectedClass'];
        $academicYear = $context['selectedAcademicYear'];
        $academicYearId = $context['selectedAcademicYearId'];

        if (!$class) {
            return redirect()->back()->with('error', 'Kelas tidak ditemukan.');
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // If month is provided, derive start/end dates
        if ($request->month && !$startDate && !$endDate) {
            $startDate = $request->month . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));
        }

        $students = $this->waliKelasStudentsQuery($class->id, $academicYearId, $context['isActiveWaliContext'])
            ->orderBy('name', 'asc')
            ->get();

        $rangeStart = $startDate ?: ($academicYear?->start_date?->toDateString() ?? '1970-01-01');
        $rangeEnd = $endDate ?: ($academicYear?->end_date?->toDateString() ?? now()->toDateString());
        $resolved = app(AttendanceSummaryService::class)
            ->resolveForRange($students->pluck('id')->all(), $rangeStart, $rangeEnd, $academicYearId ? [$academicYearId] : null);
        $studentCounts = app(AttendanceSummaryService::class)->countPerStudent($resolved);

        $periodLabel = $request->month
            ? \Carbon\Carbon::parse($request->month)->translatedFormat('F Y')
            : (($startDate && $endDate) ? "$startDate s/d $endDate" : 'Semua');

        $pdf = Pdf::loadView('walikelas.export.attendance_pdf', compact('class', 'students', 'studentCounts', 'startDate', 'endDate', 'academicYear', 'periodLabel'));
        return $pdf->download('laporan-presensi-' . $class->name . '-' . now()->format('d-m-Y') . '.pdf');
    }
}
