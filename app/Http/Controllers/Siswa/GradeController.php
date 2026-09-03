<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\StudentGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $student */
        $student = Auth::user();
        $subjectId = $request->query('subject_id');
        $academicYearId = $request->query('academic_year_id')
            ?: session('academic_year_id')
            ?: AcademicYear::where('is_active', true)->first()?->id;

        $grades = StudentGrade::where('student_id', $student->id)
            ->with(['assignment' => fn ($q) => $q->with(['class', 'subject', 'teacher'])])
            ->when($subjectId, fn ($q) => $q->whereHas('assignment', fn ($query) => $query->where('subject_id', $subjectId)))
            ->when($academicYearId, fn ($q) => $q->whereHas('assignment', fn ($query) => $query->where('academic_year_id', $academicYearId)))
            ->orderByDesc('created_at')
            ->get();

        $grades = $grades->filter(fn (StudentGrade $grade) => $grade->assignment !== null)
            ->map(function (StudentGrade $grade) {
                $assignment = $grade->assignment;
                $isOverdue = $assignment->due_date
                    && $assignment->due_date->lt(now()->startOfDay())
                    && $grade->score === null;
                $grade->setAttribute('is_overdue', (bool) $isOverdue);
                $grade->setAttribute('overdue', (int) $isOverdue);

                return $grade;
            })
            ->values();

        $academicYears = AcademicYear::orderBy('name', 'desc')->orderBy('semester')->get();
        $subjects = $grades
            ->map(fn (StudentGrade $grade) => $grade->assignment->subject)
            ->filter()
            ->unique('id')
            ->values();

        $subjectGroups = $grades
            ->groupBy(fn (StudentGrade $grade) => $grade->assignment->subject_id ?: 'none')
            ->map(function ($items) {
                $first = $items->first();
                $subject = $first->assignment->subject;
                $scores = $items->pluck('score')->filter(fn ($score) => $score !== null);
                $overdueCount = $items->where('overdue', 1)->count();
                $gradedCount = $scores->count();
                $completion = $items->count() > 0 ? round(($gradedCount / $items->count()) * 100) : 0;

                return [
                    'subject' => $subject,
                    'teacher' => $first->assignment->teacher,
                    'class' => $first->assignment->class,
                    'grades' => $items->sortByDesc(fn (StudentGrade $grade) => $grade->assignment->assigned_date?->timestamp ?? 0)->values(),
                    'assignment_count' => $items->count(),
                    'graded_count' => $gradedCount,
                    'ungraded_count' => $items->count() - $gradedCount,
                    'overdue_count' => $overdueCount,
                    'completion' => $completion,
                    'average_score' => $scores->count() ? $scores->avg() : null,
                ];
            })
            ->sortBy(fn ($group) => $group['subject']?->name ?? '')
            ->values();

        $totalAssignments = $grades->count();
        $totalGraded = $grades->filter(fn ($g) => $g->score !== null)->count();
        $totalUngraded = max(0, $totalAssignments - $totalGraded);
        $totalOverdue = $grades->sum('overdue');
        $avgScore = $grades->pluck('score')->filter(fn ($score) => $score !== null)->avg();

        return view('siswa.grades.index', compact(
            'student',
            'subjectGroups',
            'academicYears',
            'subjects',
            'subjectId',
            'academicYearId',
            'totalAssignments',
            'totalGraded',
            'totalUngraded',
            'totalOverdue',
            'avgScore'
        ));
    }

    public function show(StudentGrade $grade)
    {
        abort_unless((int) $grade->student_id === (int) Auth::id(), 403);

        $grade->load(['assignment.class', 'assignment.subject', 'assignment.teacher', 'assignment.grades']);

        return view('siswa.grades.show', compact('grade'));
    }
}
