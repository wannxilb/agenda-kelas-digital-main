<?php
// app/Http/Controllers/Wakasek/ReportController.php

namespace App\Http\Controllers\Wakasek;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Agenda;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceReportExport;
use App\Models\AcademicYear;
use App\Services\AttendanceSummaryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    use AuthorizesRequests;
    private function getAcademicYearIds(Request $request)
    {
        $input = $request->query('academic_year_id');
        $input = is_array($input) ? implode(',', $input) : $input;

        if (!$input) {
            return [];
        }

        return collect(explode(',', $input))
            ->map(fn ($id) => trim($id))
            ->filter(fn ($id) => ctype_digit((string) $id))
            ->values()
            ->all();
    }

    private function applyStudentClassFilter(Builder $query, ?int $classId, array $academicYearIds): Builder
    {
        if (!$classId) {
            if (!empty($academicYearIds)) {
                $query->where(function($q) use ($academicYearIds) {
                    $q->whereHas('classHistories', function($qh) use ($academicYearIds) {
                        $qh->whereIn('academic_year_id', $academicYearIds);
                    })->orWhere(function($qo) use ($academicYearIds) {
                        $qo->whereNotNull('class_id')
                           ->whereNotExists(function($qe) use ($academicYearIds) {
                               $qe->select(\Illuminate\Support\Facades\DB::raw(1))
                                  ->from('class_histories')
                                  ->whereColumn('class_histories.user_id', 'users.id')
                                  ->whereIn('class_histories.academic_year_id', $academicYearIds);
                           });
                    });
                });
            }

            return $query;
        }

        if (!empty($academicYearIds)) {
            return $query->inClassAndAcademicYear($classId, $academicYearIds);
        }

        return $query->where('class_id', $classId);
    }

    private function resolveRange(Request $request, \Illuminate\Support\Collection $students, ?string $startDate, ?string $endDate, array $academicYearIds): array
    {
        $studentIds = $students->pluck('id')->all();

        if (empty($studentIds)) {
            return [];
        }

        $startDate = $startDate ?: null;
        $endDate = $endDate ?: null;

        $years = AcademicYear::whereIn('id', $academicYearIds)->get();
        $rangeStart = $startDate ?? $years->min('start_date')?->toDateString() ?? '1970-01-01';
        $rangeEnd = $endDate ?? $years->max('end_date')?->toDateString() ?? now()->toDateString();

        return app(AttendanceSummaryService::class)->resolveForRange($studentIds, $rangeStart, $rangeEnd, $academicYearIds);
    }

    /**
     * Show attendance report page
     */
    public function attendance(Request $request)
    {
        $this->authorize('view', User::class);

        $institutionId = $request->user()->institution_id;
        $classes = Classes::query()
            ->when($institutionId, fn ($query) => $query->where('institution_id', $institutionId))
            ->orderBy('name')
            ->get();
        $classId = $request->class_id;
        if ($classId && !$classes->contains('id', (int) $classId)) {
            $classId = null;
        }
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        
        $academicYearIds = $this->getAcademicYearIds($request);

        $query = User::role('siswa')
            ->where('status', '!=', 'graduated')
            ->when($institutionId, fn ($query) => $query->where('institution_id', $institutionId));
        
        $this->applyStudentClassFilter($query, $classId ? (int) $classId : null, $academicYearIds);

        // Pencarian global dipakai langsung di query DB sebelum agregasi presensi,
        // sehingga hanya siswa yang cocok yang dihitung (cepat, tidak menunggu lama).
        $search = trim((string) $request->query('search'));
        if ($search !== '') {
            $needle = Str::lower($search);
            $query->where(function ($q) use ($needle) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"])
                    ->orWhereRaw('LOWER(nis) LIKE ?', ["%{$needle}%"]);
            });
        }

        $with = [
            'class',
            'classHistories' => function($q) use ($academicYearIds) {
                if (!empty($academicYearIds)) {
                    $q->whereIn('academic_year_id', $academicYearIds);
                }
                $q->with('class');
            },
        ];

        $allStudents = (clone $query)->with($with)->get();

        $resolved = $this->resolveRange($request, $allStudents, $startDate, $endDate, $academicYearIds);
        $studentCounts = app(AttendanceSummaryService::class)->countPerStudent($resolved);
        $summary = app(AttendanceSummaryService::class)->countStatuses($resolved);

        $students = $query->with($with)->orderBy('name', 'asc')->paginate(50)->withQueryString();

        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        
        return view('wakasek.reports.attendance', compact('students', 'classes', 'summary', 'academicYears', 'studentCounts', 'search'));
    }

    /**
     * Get attendance history for a specific student
     */
    public function studentAttendance(Request $request, int|string $id)
    {
        $this->authorize('view', User::class);

        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        User::query()
            ->when($request->user()->institution_id, fn ($query, $institutionId) => $query->where('institution_id', $institutionId))
            ->where('id', $id)
            ->firstOrFail();

        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $resolved = app(AttendanceSummaryService::class)->resolveForRange([(int) $id], $startDate, $endDate);

        $notes = Attendance::withoutGlobalScope('academic_year')->where('student_id', $id)
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
    
    /**
     * Export attendance report to PDF
     */
    public function exportPDF(Request $request)
    {
        $this->authorize('view', User::class);

        $institutionId = $request->user()->institution_id;
        $academicYearIds = $this->getAcademicYearIds($request);

        $studentsQuery = User::role('siswa')
            ->where('status', '!=', 'graduated')
            ->when($institutionId, fn ($query) => $query->where('institution_id', $institutionId));

        $classId = $request->class_id;
        $class = $classId
            ? Classes::query()
                ->when($institutionId, fn ($query) => $query->where('institution_id', $institutionId))
                ->find($classId)
            : null;

        $this->applyStudentClassFilter($studentsQuery, $class?->id ? (int) $class->id : null, $academicYearIds);

        $students = $studentsQuery->orderBy('name', 'asc')->get();

        $startDate = $request->has('start_date') && $request->start_date ? $request->start_date : null;
        $endDate = $request->has('end_date') && $request->end_date ? $request->end_date : null;

        $resolved = $this->resolveRange($request, $students, $startDate, $endDate, $academicYearIds);

        $studentsById = $students->keyBy('id');
        $attendances = collect();
        foreach ($resolved as $studentId => $statuses) {
            $student = $studentsById->get($studentId);
            foreach ($statuses as $date => $status) {
                $attendances->push((object) [
                    'date' => $date,
                    'status' => $status,
                    'student' => $student,
                    'note' => null,
                ]);
            }
        }

        $pdf = Pdf::loadView('wakasek.reports.attendance-pdf', compact('attendances', 'class'));
        return $pdf->download('laporan-presensi-' . date('Y-m-d') . '.pdf');
    }
    
    /**
     * Export attendance report to Excel
     */
    public function exportExcel(Request $request)
    {
        $this->authorize('view', User::class);

        $classId = $request->class_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $institutionId = $request->user()->institution_id;

        $academicYearIds = $this->getAcademicYearIds($request);
        $class = $classId
            ? Classes::query()
                ->when($institutionId, fn ($query) => $query->where('institution_id', $institutionId))
                ->find($classId)
            : null;
        $classId = $class?->id;

        $studentsQuery = User::role('siswa')
            ->where('status', '!=', 'graduated')
            ->when($institutionId, fn ($query) => $query->where('institution_id', $institutionId));
        $this->applyStudentClassFilter($studentsQuery, $classId ? (int) $classId : null, $academicYearIds);

        $students = $studentsQuery->orderBy('name', 'asc')->get();

        $resolved = $this->resolveRange($request, $students, $startDate, $endDate, $academicYearIds);
        $studentCounts = app(AttendanceSummaryService::class)->countPerStudent($resolved);

        $reportData = [];
        foreach ($students as $student) {
            $c = $studentCounts[$student->id] ?? ['total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'sick' => 0];

            $reportData[] = (object)[
                'nis' => $student->nis,
                'name' => $student->name,
                'present' => $c['present'],
                'absent' => $c['absent'],
                'late' => $c['late'],
                'excused' => $c['excused'],
                'sick' => $c['sick'],
                'total' => $c['total'],
                'percentage' => $c['total'] > 0 ? round(($c['present'] / $c['total']) * 100, 1) : 0,
            ];
        }

        $data = [
            'students' => $reportData,
        ];
        
        $filename = 'laporan-presensi-' . ($class ? $class->name . '-' : '') . date('Y-m-d') . '.xlsx';
        return Excel::download(new AttendanceReportExport($data), $filename);
    }
}
