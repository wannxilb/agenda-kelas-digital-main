<?php
// app/Http/Controllers/Guru/ReportController.php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Exports\AttendanceReportExport;
use App\Exports\GuruAgendaReportExport;
use App\Models\Agenda;
use App\Models\User;
use App\Models\Classes;
use App\Models\Attendance;
use App\Models\Schedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        /** @var User $teacher */
        $teacher = Auth::user();

        $schedules = $teacher->teachingSchedules()->with(['class', 'subject'])->get();
        $classes = $schedules->pluck('class')->unique('id')->values();
        $subjects = $schedules->pluck('subject')->filter()->unique('id')->values();

        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m', strtotime("-$i months"));
            $months[$date] = [
                'value' => $date,
                'month' => date('m', strtotime($date)),
                'year' => date('Y', strtotime($date)),
                'label' => date('F Y', strtotime($date))
            ];
        }

        $totalStudents = User::role('siswa')
            ->whereIn('class_id', $classes->pluck('id'))
            ->count();

        $stats = [
            'total_classes' => $classes->count(),
            'total_students' => $totalStudents,
            'total_attendance' => Attendance::whereIn('class_id', $classes->pluck('id'))->count(),
            'total_agendas' => Agenda::where('teacher_id', $teacher->id)->count(),
        ];

        return view('guru.report.index', compact('classes', 'subjects', 'months', 'stats'));
    }

    public function export(Request $request)
    {
        /** @var User $teacher */
        $teacher = Auth::user();

        $request->validate([
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institution_id', $teacher->institution_id)),
            ],
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|between:2000,2099',
        ]);

        $classId = $request->class_id;

        $teachesClass = Classes::where('id', $classId)
            ->whereHas('schedules', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })->exists();

        if (!$teachesClass) {
            abort(403, 'Anda tidak mengajar di kelas ini.');
        }

        $class = Classes::findOrFail($classId);

        $month = $request->month;
        $year = $request->year;
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $subjects = \App\Models\Schedule::where('teacher_id', $teacher->id)
            ->where('class_id', $class->id)
            ->with('subject')
            ->get()
            ->pluck('subject.name')
            ->unique()
            ->implode(', ');

        $students = User::role('siswa')
            ->where(function ($q) use ($class, $startDate, $endDate) {
                $q->where('class_id', $class->id)
                    ->orWhereHas('classHistories', function ($h) use ($class, $startDate, $endDate) {
                        $h->where('class_id', $class->id)
                            ->whereHas('academicYear', function ($ay) use ($startDate, $endDate) {
                                $ay->where(function ($qy) use ($startDate, $endDate) {
                                    $qy->whereNull('start_date')
                                        ->orWhere(function ($qy2) use ($startDate, $endDate) {
                                            $qy2->where('start_date', '<=', $endDate)
                                                ->where('end_date', '>=', $startDate);
                                        });
                                });
                            });
                    });
            })
            ->orderBy('name', 'asc')
            ->get();

        $resolved = app(\App\Services\AttendanceSummaryService::class)
            ->resolveForRange($students->pluck('id')->all(), $startDate, $endDate);
        $studentCounts = app(\App\Services\AttendanceSummaryService::class)->countPerStudent($resolved);

        $reportData = [];
        foreach ($students as $student) {
            $c = $studentCounts[$student->id] ?? ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'sick' => 0, 'total' => 0];

            $details = collect($resolved[$student->id] ?? [])
                ->filter(fn (string $status) => in_array($status, ['absent', 'late', 'excused', 'sick'], true))
                ->map(fn (string $status, string $date) => (object) [
                    'date' => $date,
                    'status' => $status,
                ])
                ->values();

            $reportData[] = (object)[
                'nis' => $student->nis,
                'name' => $student->name,
                'present' => $c['present'],
                'sick' => $c['sick'],
                'absent' => $c['absent'],
                'late' => $c['late'],
                'excused' => $c['excused'],
                'total' => $c['total'],
                'percentage' => $c['total'] > 0 ? round(($c['present'] / $c['total']) * 100, 1) : 0,
                'details' => $details,
            ];
        }

        $data = [
            'class' => $class,
            'month_name' => date('F Y', strtotime($startDate)),
            'month' => $month,
            'year' => $year,
            'students' => $reportData,
            'teacher' => $teacher,
            'subjects' => $subjects ?: 'Semua Mata Pelajaran',
            'print_date' => now()->translatedFormat('d F Y H:i'),
        ];

        return Excel::download(new AttendanceReportExport($data), "laporan-presensi-{$class->name}-{$month}-{$year}.xlsx");
    }

    public function exportAgendaExcel(Request $request)
    {
        /** @var User $teacher */
        $teacher = Auth::user();
        $data = $this->buildAgendaReportData($request, $teacher);

        return Excel::download(
            new GuruAgendaReportExport($data),
            'rekap-agenda-' . $data['start_date']->format('Ymd') . '-' . $data['end_date']->format('Ymd') . '.xlsx'
        );
    }

    public function exportAgendaPdf(Request $request)
    {
        /** @var User $teacher */
        $teacher = Auth::user();
        $data = $this->buildAgendaReportData($request, $teacher);

        $pdf = Pdf::loadView('guru.report.agenda_pdf', $data)->setPaper('a4', 'landscape');

        return $pdf->download('rekap-agenda-' . $data['start_date']->format('Ymd') . '-' . $data['end_date']->format('Ymd') . '.pdf');
    }

    private function buildAgendaReportData(Request $request, User $teacher): array
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'class_id' => [
                'nullable',
                Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institution_id', $teacher->institution_id)),
            ],
            'subject_id' => [
                'nullable',
                Rule::exists('subjects', 'id')->where(fn ($q) => $q->where('institution_id', $teacher->institution_id)),
            ],
            'status' => 'nullable|in:all,published,draft',
        ]);

        if (!empty($validated['class_id'])) {
            $teachesClass = Classes::where('id', $validated['class_id'])
                ->whereHas('schedules', fn ($q) => $q->where('teacher_id', $teacher->id))
                ->exists();

            if (!$teachesClass) {
                abort(403, 'Anda tidak mengajar di kelas ini.');
            }
        }

        if (!empty($validated['subject_id'])) {
            $teachesSubject = Schedule::where('teacher_id', $teacher->id)
                ->where('subject_id', $validated['subject_id'])
                ->exists();

            if (!$teachesSubject) {
                abort(403, 'Anda tidak mengajar mata pelajaran ini.');
            }
        }

        $startDate = \Carbon\Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = \Carbon\Carbon::parse($validated['end_date'])->endOfDay();
        $status = $validated['status'] ?? 'published';

        $agendas = Agenda::with(['class', 'subject', 'schedule'])
            ->where('teacher_id', $teacher->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->when(!empty($validated['class_id']), fn ($q) => $q->where('class_id', $validated['class_id']))
            ->when(!empty($validated['subject_id']), fn ($q) => $q->where('subject_id', $validated['subject_id']))
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderBy('date')
            ->orderBy('created_at')
            ->get();

        $className = empty($validated['class_id'])
            ? 'Semua kelas'
            : (Classes::find($validated['class_id'])?->name ?: 'Semua kelas');

        $subjectName = empty($validated['subject_id'])
            ? 'Semua mata pelajaran'
            : (\App\Models\Subject::find($validated['subject_id'])?->name ?: 'Semua mata pelajaran');

        return [
            'teacher' => $teacher,
            'agendas' => $agendas,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'class_name' => $className,
            'subject_name' => $subjectName,
            'status_label' => match ($status) {
                'draft' => 'Draft',
                'all' => 'Semua status',
                default => 'Published',
            },
            'print_date' => now()->translatedFormat('d F Y H:i'),
        ];
    }
}
