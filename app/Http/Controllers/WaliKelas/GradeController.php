<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\GradeAssignment;
use App\Models\StudentGrade;
use App\Models\Subject;
use App\Traits\ResolvesWaliKelasContext;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GradeController extends Controller
{
    use ResolvesWaliKelasContext;

    public function index(Request $request)
    {
        $context = $this->waliKelasContext($request);
        $class = $context['selectedClass'];
        $academicYearId = $context['selectedAcademicYearId'];

        $subjectId = $request->query('subject_id');
        $search = trim((string) $request->query('search'));

        $academicYears = AcademicYear::orderBy('name', 'desc')->orderBy('semester')->get();

        if (!$class) {
            return view('walikelas.grades.index', array_merge($context, [
                'has_class' => false,
                'students' => collect(),
                'subjectGroups' => collect(),
                'subjects' => collect(),
                'academicYears' => $academicYears,
                'subjectId' => $subjectId,
                'search' => $search,
                'totalStudents' => 0,
                'totalAssignments' => 0,
                'totalGraded' => 0,
                'totalOverdue' => 0,
                'avgScore' => null,
            ]));
        }

        $studentQuery = $this->waliKelasStudentsQuery(
            $class->id,
            $academicYearId,
            $context['isActiveWaliContext']
        );

        if ($search !== '') {
            $searchTerm = mb_strtolower($search);
            $studentQuery->where(function ($query) use ($searchTerm) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(nis) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(nisn) LIKE ?', ["%{$searchTerm}%"]);
            });
        }

        $students = $studentQuery
            ->orderByRaw('LOWER(name) ASC')
            ->get();

        $studentIds = $students->pluck('id');

        $subjects = GradeAssignment::where('class_id', $class->id)
            ->when($academicYearId, fn ($query) => $query->where('academic_year_id', $academicYearId))
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $grades = StudentGrade::whereIn('student_id', $studentIds)
            ->whereHas('assignment', function ($query) use ($class, $academicYearId, $subjectId) {
                $query->where('class_id', $class->id)
                    ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
                    ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId));
            })
            ->with(['assignment' => fn ($q) => $q->with('subject')])
            ->get();

        $grades = $grades->filter(fn (StudentGrade $grade) => $grade->assignment !== null);

        $this->attachOverdueStatus($grades);

        $subjectGroups = $grades
            ->groupBy(fn (StudentGrade $grade) => $grade->assignment->subject_id ?: 'none')
            ->map(function ($items) {
                $first = $items->first();
                $subject = $first->assignment->subject;
                $scores = $items->pluck('score')->filter(fn ($score) => $score !== null);
                $overdueCount = $items->where('overdue', 1)->count();

                return [
                    'subject' => $subject,
                    'assignment_count' => $items->map(fn ($g) => $g->assignment_id)->unique()->count(),
                    'graded_count' => $scores->count(),
                    'overdue_count' => $overdueCount,
                    'average_score' => $scores->count() ? $scores->avg() : null,
                ];
            })
            ->sortBy(fn ($group) => $group['subject']?->name ?? '')
            ->values();

        $totalAssignments = $grades->map(fn ($g) => $g->assignment_id)->unique()->count();
        $totalGraded = $grades->where('score', '!==', null)->count();
        $totalOverdue = $grades->sum('overdue');
        $avgScore = $grades->pluck('score')->filter(fn ($score) => $score !== null)->avg();

        return view('walikelas.grades.index', array_merge($context, [
            'has_class' => true,
            'class' => $class,
            'students' => $students,
            'grades' => $grades,
            'subjectGroups' => $subjectGroups,
            'subjects' => $subjects,
            'academicYears' => $academicYears,
            'subjectId' => $subjectId,
            'search' => $search,
            'totalStudents' => $students->count(),
            'totalAssignments' => $totalAssignments,
            'totalGraded' => $totalGraded,
            'totalOverdue' => $totalOverdue,
            'avgScore' => $avgScore,
        ]));
    }

    private function attachOverdueStatus(Collection $grades): void
    {
        $today = now()->startOfDay();

        $grades->each(function (StudentGrade $grade) use ($today) {
            $assignment = $grade->assignment;
            $isOverdue = $assignment->due_date
                && $assignment->due_date->lt($today)
                && $grade->score === null;

            $grade->setAttribute('overdue', (int) $isOverdue);
        });
    }
}
