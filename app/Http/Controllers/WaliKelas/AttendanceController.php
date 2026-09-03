<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\StudentDailyAttendance;
use App\Models\StudentEarlyLeaveRequest;
use App\Services\AttendanceSummaryService;
use App\Traits\ResolvesWaliKelasContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AttendanceController extends Controller
{
    use ResolvesWaliKelasContext;

    public function index(Request $request)
    {
        $context = $this->waliKelasContext($request);
        $class = $context['selectedClass'];
        $academicYearId = $context['selectedAcademicYearId'];

        if (! $class) {
            return view('walikelas.attendance.index', array_merge($context, ['has_class' => false]));
        }

        $date = $request->input('date', date('Y-m-d'));
        $search = trim((string) $request->query('search'));
        $statusFilter = $request->query('status', 'all');

        $studentQuery = $this->waliKelasStudentsQuery($class->id, $academicYearId, $context['isActiveWaliContext']);

        if ($search !== '') {
            $search = mb_strtolower($search);
            $studentQuery->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(nis) LIKE ?', ["%{$search}%"]);
            });
        }

        $students = $studentQuery
            ->with(['attendances' => function ($q) use ($date) {
                $q->withoutGlobalScope('academic_year')
                    ->whereDate('date', $date);
            }])
            ->orderByRaw('LOWER(name) ASC')
            ->get();

        $resolvedStatuses = app(AttendanceSummaryService::class)
            ->resolveForDate($students->pluck('id')->all(), $date);

        $totalStudentsBeforeStatusFilter = $students->count();

        if (in_array($statusFilter, ['present', 'excused', 'sick', 'late', 'absent', 'none'], true)) {
            $students = $students
                ->filter(function ($student) use ($resolvedStatuses, $statusFilter) {
                    $status = $resolvedStatuses[$student->id] ?? $student->attendances->first()?->status;
                    $status = $status === 'not_yet' ? null : $status;

                    return $statusFilter === 'none' ? ! $status : $status === $statusFilter;
                })
                ->values();
        }

        $approvedAbsences = StudentEarlyLeaveRequest::whereIn('student_id', $students->pluck('id'))
            ->where('status', 'approved')
            ->whereDate('date', '<=', $date)
            ->whereRaw('COALESCE(date_end, date) >= ?', [$date])
            ->get()
            ->filter(fn (StudentEarlyLeaveRequest $request) => $request->isTidakHadirCategory())
            ->keyBy('student_id');

        return view('walikelas.attendance.index', array_merge($context, [
            'has_class' => true,
            'class' => $class,
            'students' => $students,
            'date' => $date,
            'resolvedStatuses' => $resolvedStatuses,
            'approvedAbsences' => $approvedAbsences,
            'totalStudentsBeforeStatusFilter' => $totalStudentsBeforeStatusFilter,
        ]));
    }

    public function report(Request $request)
    {
        $context = $this->waliKelasContext($request);
        $class = $context['selectedClass'];
        $academicYearId = $context['selectedAcademicYearId'];

        if (! $class) {
            return redirect()->route('wali-kelas.dashboard')->with('error', 'Anda belum terdaftar di kelas manapun.');
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $query = $this->waliKelasStudentsQuery($class->id, $academicYearId, $context['isActiveWaliContext']);

        $search = trim((string) $request->query('search'));
        if ($search !== '') {
            $search = mb_strtolower($search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(nis) LIKE ?', ["%{$search}%"]);
            });
        }

        $allStudents = (clone $query)->with('class')->orderBy('name', 'asc')->get();
        $students = $query->with('class')->orderBy('name', 'asc')->paginate(50)->withQueryString();

        $rangeStart = $startDate ?: '1970-01-01';
        $rangeEnd = $endDate ?: now()->toDateString();

        $resolved = $this->resolveReportStatuses(
            $allStudents->pluck('id')->all(),
            $rangeStart,
            $rangeEnd,
            $startDate,
            $endDate
        );
        $studentCounts = app(AttendanceSummaryService::class)->countPerStudent($resolved);
        $summary = app(AttendanceSummaryService::class)->countStatuses($resolved);

        return view('walikelas.attendance.report', array_merge($context, compact('class', 'students', 'summary', 'studentCounts')));
    }

    public function studentAttendance(Request $request, int|string $id)
    {
        $context = $this->waliKelasContext($request);
        $class = $context['selectedClass'];
        $academicYearId = $context['selectedAcademicYearId'];

        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        abort_if(! $class, 404);

        $this->waliKelasStudentsQuery($class->id, $academicYearId, $context['isActiveWaliContext'])
            ->where('id', $id)
            ->firstOrFail();

        $startDate = Carbon::create((int) $year, (int) $month, 1)->toDateString();
        $endDate = Carbon::parse($startDate)->endOfMonth()->toDateString();
        $resolved = app(AttendanceSummaryService::class)
            ->resolveForRange([(int) $id], $startDate, $endDate, $academicYearId ? [$academicYearId] : null);

        $notes = Attendance::withoutGlobalScope('academic_year')
            ->where('student_id', $id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn ($att) => $att->date->toDateString());

        $attendances = collect($resolved[(int) $id] ?? [])
            ->map(fn (string $status, string $date) => [
                'date' => $date,
                'status' => $status,
                'note' => $notes->get($date)?->note,
            ])
            ->sortByDesc('date')
            ->values();

        return response()->json(compact('attendances'));
    }

    private function resolveReportStatuses(array $studentIds, string $rangeStart, string $rangeEnd, ?string $startDate, ?string $endDate): array
    {
        if (empty($studentIds)) {
            return [];
        }

        $dates = $this->reportActivityDates($studentIds, $rangeStart, $rangeEnd);

        if ($startDate && $endDate && $startDate === $endDate) {
            $dates->push($startDate);
        }

        $dates = $dates->unique()->sort()->values();
        $service = app(AttendanceSummaryService::class);
        $resolved = [];

        foreach ($dates as $date) {
            foreach ($service->resolveForDate($studentIds, $date) as $studentId => $status) {
                if ($status && $status !== 'not_yet') {
                    $resolved[$studentId][$date] = $status;
                }
            }
        }

        return $resolved;
    }

    private function reportActivityDates(array $studentIds, string $rangeStart, string $rangeEnd): Collection
    {
        $dates = collect();

        $dates = $dates->merge(
            Attendance::withoutGlobalScope('academic_year')
                ->whereIn('student_id', $studentIds)
                ->whereDate('date', '>=', $rangeStart)
                ->whereDate('date', '<=', $rangeEnd)
                ->pluck('date')
                ->map(fn ($date) => Carbon::parse($date)->toDateString())
        );

        $dates = $dates->merge(
            StudentDailyAttendance::withoutGlobalScope('academic_year')
                ->whereIn('student_id', $studentIds)
                ->whereDate('date', '>=', $rangeStart)
                ->whereDate('date', '<=', $rangeEnd)
                ->pluck('date')
                ->map(fn ($date) => Carbon::parse($date)->toDateString())
        );

        StudentEarlyLeaveRequest::whereIn('student_id', $studentIds)
            ->where('status', 'approved')
            ->whereDate('date', '<=', $rangeEnd)
            ->where(function ($query) use ($rangeStart) {
                $query->whereDate('date_end', '>=', $rangeStart)
                    ->orWhereNull('date_end');
            })
            ->get(['date', 'date_end'])
            ->each(function (StudentEarlyLeaveRequest $request) use ($rangeStart, $rangeEnd, &$dates) {
                $from = Carbon::parse($request->date)->max(Carbon::parse($rangeStart));
                $to = Carbon::parse($request->effectiveEndDate())->min(Carbon::parse($rangeEnd));

                for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                    $dates->push($date->toDateString());
                }
            });

        return $dates;
    }
}
