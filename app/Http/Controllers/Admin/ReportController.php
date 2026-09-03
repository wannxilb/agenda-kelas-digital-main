<?php
// app/Http/Controllers/Admin/ReportController.php

namespace App\Http\Controllers\Admin;

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
use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{
    /** Masa berlaku cache rekap presensi (detik). */
    private const REPORT_CACHE_TTL = 60;

    private function getAcademicYearIds(Request $request)
    {
        $input = $request->query('academic_year_id');
        $input = is_array($input) ? implode(',', $input) : $input;
        
        if ($input) {
            return explode(',', $input);
        }

        $activeYear = AcademicYear::where('is_active', true)->first();
        return $activeYear ? [$activeYear->id] : [];
    }

    /**
     * Batasi periode default ke bulan berjalan supaya rekap tidak memuat
     * seluruh tahun ajaran ke memory. Tanggal eksplisit dari user tetap dihormati.
     *
     * @return array{0:string, 1:string} [startDate, endDate]
     */
    private function defaultReportDates(?string $startDate, ?string $endDate): array
    {
        $start = $startDate ?: now()->startOfMonth()->toDateString();
        $end = $endDate ?: now()->endOfMonth()->toDateString();

        // Jika hanya satu batas yang diisi dan default bulan ini membuat range
        // terbalik (mis. start di masa depan / end di masa lalu), ikuti batas user.
        if ($start > $end) {
            if ($startDate && !$endDate) {
                $end = $start;
            } elseif ($endDate && !$startDate) {
                $start = $end;
            }
        }

        return [$start, $end];
    }

    private function resolveRange(array $studentIds, string $startDate, string $endDate, array $academicYearIds): array
    {
        if (empty($studentIds)) {
            return [];
        }

        sort($studentIds);
        $institutionKey = auth()->user()?->institution_id ?? 'global';
        $cacheKey = 'admin.reports.attendance.v1:' . md5(json_encode([
            'institution' => $institutionKey,
            'students' => $studentIds,
            'start' => $startDate,
            'end' => $endDate,
            'years' => $academicYearIds,
        ]));

        return Cache::remember($cacheKey, self::REPORT_CACHE_TTL, function () use ($studentIds, $startDate, $endDate, $academicYearIds) {
            return app(AttendanceSummaryService::class)->resolveForRange($studentIds, $startDate, $endDate, $academicYearIds);
        });
    }

    /**
     * Show attendance report page
     */
    public function attendance(Request $request)
    {
        $classes = Classes::orderBy('name')->get();
        $classId = $request->class_id;
        
        $academicYearIds = $this->getAcademicYearIds($request);

        $query = User::role('siswa')->where('status', '!=', 'graduated');
        
        if ($classId) {
            $query->inClassAndAcademicYear($classId, $academicYearIds);
        } else {
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

        // Ambil siswa dengan relasi presensi yang difilter (halaman utama, dipaginasi)
        $students = $query->with([
            'class', 
            'classHistories' => function($q) use ($academicYearIds) {
                $q->whereIn('academic_year_id', $academicYearIds)->with('class');
            },
        ])->orderBy('name', 'asc')->paginate(50);

        // Cukup ambil ID siswa (tanpa hydrate model) untuk perhitungan rekap summary.
        $allStudentIds = (clone $query)->pluck('id')->all();

        [$startDate, $endDate] = $this->defaultReportDates($request->start_date, $request->end_date);

        $resolved = $this->resolveRange($allStudentIds, $startDate, $endDate, $academicYearIds);
        $studentCounts = app(AttendanceSummaryService::class)->countPerStudent($resolved);
        $summary = app(AttendanceSummaryService::class)->countStatuses($resolved);
        
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        
        return view('admin.reports.attendance', compact('students', 'classes', 'summary', 'academicYears', 'studentCounts', 'startDate', 'endDate'));
    }

    /**
     * Get attendance history for a specific student
     */
    public function studentAttendance(Request $request, int|string $id)
    {
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

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
        $academicYearIds = $this->getAcademicYearIds($request);

        $query = User::role('siswa')->where('status', '!=', 'graduated');
        
        if ($request->has('class_id') && $request->class_id) {
            $query->inClassAndAcademicYear($request->class_id, $academicYearIds);
        } else {
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

        $students = $query->orderBy('name', 'asc')->get();

        [$startDate, $endDate] = $this->defaultReportDates($request->start_date, $request->end_date);

        $resolved = $this->resolveRange($students->pluck('id')->all(), $startDate, $endDate, $academicYearIds);

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

        $class = $request->class_id ? Classes::find($request->class_id) : null;
        
        $pdf = Pdf::loadView('admin.reports.attendance-pdf', compact('attendances', 'class'));
        return $pdf->download('laporan-presensi-' . date('Y-m-d') . '.pdf');
    }
    
    /**
     * Export attendance report to Excel
     */
    public function exportExcel(Request $request)
    {
        $classId = $request->class_id;

        $academicYearIds = $this->getAcademicYearIds($request);

        $studentsQuery = User::role('siswa')->where('status', '!=', 'graduated');
        if ($classId) {
            $studentsQuery->inClassAndAcademicYear($classId, $academicYearIds);
        } else {
            $studentsQuery->where(function($q) use ($academicYearIds) {
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

        $students = $studentsQuery->orderBy('name', 'asc')->get();

        [$startDate, $endDate] = $this->defaultReportDates($request->start_date, $request->end_date);

        $resolved = $this->resolveRange($students->pluck('id')->all(), $startDate, $endDate, $academicYearIds);
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
        
        $filename = 'laporan-presensi-' . ($classId ? (Classes::find($classId)?->name . '-') : '') . date('Y-m-d') . '.xlsx';
        return Excel::download(new AttendanceReportExport($data), $filename);
    }
}
