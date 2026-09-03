<?php

namespace App\Http\Controllers\Guru;

use App\Exports\GradeAssignmentExport;
use App\Exports\GradeSummaryExport;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\GradeAssignment;
use App\Models\Schedule;
use App\Models\StudentGrade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user();
        $classId = $request->query('class_id');
        $subjectId = $request->query('subject_id');
        $academicYearId = $request->query('academic_year_id');
        $assignedDate = $request->query('assigned_date');
        $search = $request->query('search');

        $classes = $this->teachingClasses($teacher->id);
        $subjects = $this->teachingSubjects($teacher->id);
        $academicYears = AcademicYear::orderBy('name', 'desc')
            ->orderBy('semester')
            ->get();
        $selectedAcademicYear = $academicYearId
            ? $academicYears->firstWhere('id', (int) $academicYearId)
            : AcademicYear::where('is_active', true)->first();

        $assignments = GradeAssignment::where('teacher_id', $teacher->id)
            ->with(['class', 'subject', 'academicYear', 'grades:id,grade_assignment_id,score'])
            ->withCount('grades')
            ->withCount(['grades as graded_count' => fn ($q) => $q->whereNotNull('score')])
            ->withAvg(['grades as average_score' => fn ($q) => $q->whereNotNull('score')], 'score')
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->when($assignedDate, fn ($q) => $q->whereDate('assigned_date', $assignedDate))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->whereRaw('LOWER(title) LIKE ?', ["%" . mb_strtolower($search) . "%"])
                        ->orWhereRaw('LOWER(description) LIKE ?', ["%" . mb_strtolower($search) . "%"]);
                });
            })
            ->latest('assigned_date')
            ->latest()
            ->get();
        $this->attachOverdueStatus($assignments);

        $semesterGroups = $assignments
            ->groupBy(fn ($assignment) => implode(':', [
                $assignment->academic_year_id ?: 'none',
                $assignment->class_id ?: 'none',
                $assignment->subject_id ?: 'none',
            ]))
            ->map(function ($items) {
                $first = $items->first();
                $sortedAssignments = $items->sortByDesc(fn ($assignment) => $assignment->assigned_date?->timestamp ?? 0)->values();
                $scores = $items->flatMap(fn ($assignment) => $assignment->grades)
                    ->pluck('score')
                    ->filter(fn ($score) => $score !== null);

                return [
                    'academic_year' => $first->academicYear,
                    'class' => $first->class,
                    'subject' => $first->subject,
                    'assignments' => $sortedAssignments,
                    'assignment_count' => $items->count(),
                    'graded_count' => $items->sum('graded_count'),
                    'grades_count' => $items->sum('grades_count'),
                    'overdue_count' => $items->sum('overdue_count'),
                    'average_score' => $scores->count() ? $scores->avg() : null,
                    'latest_date' => $sortedAssignments->first()?->assigned_date,
                ];
            })
            ->sortBy([
                ['class.name', 'asc'],
                ['subject.name', 'asc'],
            ])
            ->values();

        $totalAssignments = $assignments->count();
        $totalGraded = $assignments->sum('graded_count');
        $totalStudents = $assignments->sum('grades_count');
        $totalUngraded = max(0, $totalStudents - $totalGraded);
        $totalOverdue = $assignments->sum('overdue_count');
        $avgScore = $assignments
            ->flatMap(fn ($assignment) => $assignment->grades)
            ->pluck('score')
            ->filter(fn ($score) => $score !== null)
            ->avg();

        return view('guru.grades.index', compact(
            'academicYears',
            'selectedAcademicYear',
            'semesterGroups',
            'classes',
            'subjects',
            'classId',
            'subjectId',
            'academicYearId',
            'assignedDate',
            'search',
            'totalAssignments',
            'totalGraded',
            'totalStudents',
            'totalUngraded',
            'totalOverdue',
            'avgScore'
        ));
    }

    public function create(Request $request)
    {
        $teacher = Auth::user();
        $classes = $this->teachingClasses($teacher->id);
        $subjects = $this->teachingSubjects($teacher->id);
        $selectedClassId = $request->query('class_id') ?: old('class_id');
        $selectedSubjectId = $request->query('subject_id') ?: old('subject_id');
        $students = $selectedClassId ? $this->studentsForClass($selectedClassId) : collect();
        $scheduleMap = $this->scheduleMap($teacher->id);

        return view('guru.grades.create', compact('classes', 'subjects', 'students', 'selectedClassId', 'selectedSubjectId', 'scheduleMap'));
    }

    public function store(Request $request)
    {
        $teacher = Auth::user();

        $validated = $this->validateAssignment($request);
        $this->abortUnlessTeacherSchedule($teacher->id, $validated['class_id'], $validated['subject_id']);

        $students = $this->studentsForClass($validated['class_id']);
        $studentIds = $students->pluck('id')->map(fn ($id) => (string) $id)->all();
        $scores = $request->input('scores', []);
        $notes = $request->input('notes', []);

        DB::transaction(function () use ($validated, $teacher, $students, $scores, $notes) {
            $assignment = GradeAssignment::create([
                'teacher_id' => $teacher->id,
                'class_id' => $validated['class_id'],
                'subject_id' => $validated['subject_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'assigned_date' => $validated['assigned_date'],
                'due_date' => $validated['due_date'] ?? null,
                'max_score' => $validated['max_score'],
                'institution_id' => $teacher->institution_id,
            ]);

            foreach ($students as $student) {
                $rawScore = $scores[$student->id] ?? null;
                StudentGrade::create([
                    'grade_assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                    'score' => $rawScore === '' ? null : $rawScore,
                    'note' => $notes[$student->id] ?? null,
                    'institution_id' => $teacher->institution_id,
                ]);
            }
        });

        return redirect()->route('guru.grades.index')->with('success', 'Nilai tugas berhasil disimpan.');
    }

    public function show(GradeAssignment $grade)
    {
        $this->authorizeAssignment($grade);
        $grade->load(['class', 'subject', 'grades.student']);
        $this->attachOverdueStatus(collect([$grade]));

        return view('guru.grades.show', ['assignment' => $grade]);
    }

    public function export(GradeAssignment $grade)
    {
        $this->authorizeAssignment($grade);
        $grade->load(['class', 'subject', 'grades.student']);

        $filename = 'nilai-tugas-' . Str::slug($grade->title ?: 'tugas') . '-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new GradeAssignmentExport($grade), $filename);
    }

    public function summary(Request $request, Classes $class, Subject $subject)
    {
        $teacher = Auth::user();
        $academicYear = $this->selectedAcademicYear($request);

        $summary = $this->gradeSummaryData($teacher->id, $class, $subject, $academicYear);

        return view('guru.grades.summary', array_merge($summary, [
            'class' => $class,
            'subject' => $subject,
            'academicYear' => $academicYear,
        ]));
    }

    public function exportSummary(Request $request, Classes $class, Subject $subject)
    {
        $teacher = Auth::user();
        $academicYear = $this->selectedAcademicYear($request);
        $summary = $this->gradeSummaryData($teacher->id, $class, $subject, $academicYear);

        $filename = 'rekap-nilai-' . Str::slug(($class->name ?? 'kelas') . '-' . ($subject->name ?? 'mapel')) . '-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new GradeSummaryExport(
            $class,
            $subject,
            $academicYear,
            $summary['students'],
            $summary['assignments'],
            $summary['gradeMatrix']
        ), $filename);
    }

    public function edit(GradeAssignment $grade)
    {
        $this->authorizeAssignment($grade);
        $teacher = Auth::user();
        $classes = $this->teachingClasses($teacher->id);
        $subjects = $this->teachingSubjects($teacher->id);
        $students = $this->studentsForClass($grade->class_id);
        $grade->load('grades');
        $gradeMap = $grade->grades->keyBy('student_id');
        $scheduleMap = $this->scheduleMap($teacher->id);

        return view('guru.grades.edit', compact('grade', 'classes', 'subjects', 'students', 'gradeMap', 'scheduleMap'));
    }

    public function update(Request $request, GradeAssignment $grade)
    {
        $this->authorizeAssignment($grade);
        $teacher = Auth::user();

        $validated = $this->validateAssignment($request);
        $this->abortUnlessTeacherSchedule($teacher->id, $validated['class_id'], $validated['subject_id']);

        $students = $this->studentsForClass($validated['class_id']);
        $scores = $request->input('scores', []);
        $notes = $request->input('notes', []);

        DB::transaction(function () use ($grade, $validated, $teacher, $students, $scores, $notes) {
            $grade->update([
                'class_id' => $validated['class_id'],
                'subject_id' => $validated['subject_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'assigned_date' => $validated['assigned_date'],
                'due_date' => $validated['due_date'] ?? null,
                'max_score' => $validated['max_score'],
                'institution_id' => $teacher->institution_id,
            ]);

            $validStudentIds = $students->pluck('id')->all();
            $grade->grades()->whereNotIn('student_id', $validStudentIds)->delete();

            foreach ($students as $student) {
                $rawScore = $scores[$student->id] ?? null;
                StudentGrade::updateOrCreate(
                    [
                        'grade_assignment_id' => $grade->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'score' => $rawScore === '' ? null : $rawScore,
                        'note' => $notes[$student->id] ?? null,
                        'institution_id' => $teacher->institution_id,
                    ]
                );
            }
        });

        return redirect()->route('guru.grades.show', $grade)->with('success', 'Nilai tugas berhasil diperbarui.');
    }

    public function destroy(GradeAssignment $grade)
    {
        $this->authorizeAssignment($grade);
        $grade->delete();

        return redirect()->route('guru.grades.index')->with('success', 'Data nilai tugas berhasil dihapus.');
    }

    private function validateAssignment(Request $request): array
    {
        $maxScore = (float) $request->input('max_score', 100);

        return $request->validate([
            'class_id' => [
                'required',
                'integer',
                Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institution_id', Auth::user()->institution_id)),
            ],
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')->where(fn ($q) => $q->where('institution_id', Auth::user()->institution_id)),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'assigned_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:assigned_date'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:999.99'],
            'scores' => ['nullable', 'array'],
            'scores.*' => ['nullable', 'numeric', 'min:0', 'max:' . $maxScore],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string', 'max:500'],
        ], [
            'class_id.required' => 'Kelas wajib dipilih.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'title.required' => 'Nama tugas wajib diisi.',
            'description.required' => 'Keterangan tugas wajib diisi.',
            'assigned_date.required' => 'Tanggal tugas wajib diisi.',
            'assigned_date.date' => 'Tanggal tugas tidak valid.',
            'due_date.date' => 'Batas pengumpulan tidak valid.',
            'due_date.after_or_equal' => 'Batas pengumpulan harus sama dengan atau setelah tanggal tugas.',
            'max_score.required' => 'Nilai maksimal wajib diisi.',
            'max_score.numeric' => 'Nilai maksimal harus berupa angka.',
            'max_score.min' => 'Nilai maksimal minimal 1.',
            'max_score.max' => 'Nilai maksimal maksimal 999.99.',
            'scores.*.numeric' => 'Nilai siswa harus berupa angka.',
            'scores.*.min' => 'Nilai siswa tidak boleh kurang dari 0.',
            'scores.*.max' => 'Nilai siswa tidak boleh melebihi nilai maksimal.',
            'notes.*.max' => 'Keterangan nilai siswa maksimal 500 karakter.',
        ]);
    }

    private function abortUnlessTeacherSchedule(int $teacherId, int $classId, int $subjectId): void
    {
        $hasSchedule = Schedule::where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->exists();

        abort_unless($hasSchedule, 403);
    }

    private function authorizeAssignment(GradeAssignment $assignment): void
    {
        abort_unless($assignment->teacher_id === Auth::id(), 403);
    }

    private function selectedAcademicYear(Request $request): ?AcademicYear
    {
        $academicYearId = $request->query('academic_year_id');

        if ($academicYearId && ctype_digit((string) $academicYearId)) {
            return AcademicYear::find($academicYearId);
        }

        $sessionYearId = $request->session()->get('academic_year_id');
        if ($sessionYearId && ctype_digit((string) $sessionYearId)) {
            $sessionYear = AcademicYear::find($sessionYearId);
            if ($sessionYear) {
                return $sessionYear;
            }
        }

        return AcademicYear::where('is_active', true)->first();
    }

    private function gradeSummaryData(int $teacherId, Classes $class, Subject $subject, ?AcademicYear $academicYear): array
    {
        $this->abortUnlessTeacherSchedule($teacherId, $class->id, $subject->id);

        $academicYearIds = $academicYear ? [$academicYear->id] : AcademicYear::where('is_active', true)->pluck('id')->all();
        $students = $this->studentsForClass($class->id, $academicYearIds);

        $assignments = GradeAssignment::withoutGlobalScope('academic_year')
            ->where('teacher_id', $teacherId)
            ->where('class_id', $class->id)
            ->where('subject_id', $subject->id)
            ->when(!empty($academicYearIds), fn ($q) => $q->whereIn('academic_year_id', $academicYearIds))
            ->with(['grades' => fn ($q) => $q->select('id', 'grade_assignment_id', 'student_id', 'score', 'note')])
            ->orderBy('assigned_date')
            ->orderBy('id')
            ->get();
        $this->attachOverdueStatus($assignments);

        $gradeMatrix = $assignments
            ->flatMap(fn ($assignment) => $assignment->grades)
            ->keyBy(fn ($grade) => $grade->student_id . ':' . $grade->grade_assignment_id);

        $studentSummaries = $students->map(function ($student) use ($assignments, $gradeMatrix) {
            $scores = $assignments
                ->map(fn ($assignment) => $gradeMatrix->get($student->id . ':' . $assignment->id)?->score)
                ->filter(fn ($score) => $score !== null)
                ->map(fn ($score) => (float) $score);

            return [
                'student_id' => $student->id,
                'filled_count' => $scores->count(),
                'average_score' => $scores->count() ? $scores->avg() : null,
            ];
        })->keyBy('student_id');

        $filledCount = $gradeMatrix->filter(fn ($grade) => $grade->score !== null)->count();
        $totalCells = $students->count() * $assignments->count();
        $overdueCells = $assignments->sum('overdue_count');
        $averageScore = $gradeMatrix
            ->pluck('score')
            ->filter(fn ($score) => $score !== null)
            ->avg();

        return compact('students', 'assignments', 'gradeMatrix', 'studentSummaries', 'filledCount', 'totalCells', 'overdueCells', 'averageScore');
    }

    private function attachOverdueStatus(Collection $assignments): void
    {
        $today = now()->startOfDay();

        $assignments->each(function (GradeAssignment $assignment) use ($today) {
            $gradesCount = (int) ($assignment->grades_count ?? $assignment->grades->count());
            $gradedCount = (int) ($assignment->graded_count ?? $assignment->grades->whereNotNull('score')->count());
            $missingCount = max(0, $gradesCount - $gradedCount);
            $isOverdue = $assignment->due_date
                && $assignment->due_date->lt($today)
                && $missingCount > 0;

            $assignment->setAttribute('missing_count', $missingCount);
            $assignment->setAttribute('is_overdue', (bool) $isOverdue);
            $assignment->setAttribute('overdue_count', $isOverdue ? $missingCount : 0);
        });
    }

    private function teachingClasses(int $teacherId)
    {
        $classIds = Schedule::where('teacher_id', $teacherId)->pluck('class_id')->unique();

        return Classes::whereIn('id', $classIds)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
    }

    private function teachingSubjects(int $teacherId)
    {
        $subjectIds = Schedule::where('teacher_id', $teacherId)->pluck('subject_id')->unique();

        return Subject::whereIn('id', $subjectIds)
            ->orderBy('name')
            ->get();
    }

    private function studentsForClass(int|string $classId, ?array $academicYearIds = null): Collection
    {
        $academicYearIds = $academicYearIds ?? AcademicYear::where('is_active', true)->pluck('id')->all();

        $query = User::role('siswa')
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', '!=', 'graduated');
            });

        if (!empty($academicYearIds)) {
            $query->inClassAndAcademicYear($classId, $academicYearIds);
        } else {
            $query->where('class_id', $classId);
        }

        return $query
            ->orderByRaw('LOWER(name) ASC')
            ->get();
    }

    private function scheduleMap(int $teacherId): array
    {
        return Schedule::where('teacher_id', $teacherId)
            ->get(['class_id', 'subject_id'])
            ->groupBy('class_id')
            ->map(fn ($items) => $items->pluck('subject_id')->unique()->values())
            ->toArray();
    }
}
