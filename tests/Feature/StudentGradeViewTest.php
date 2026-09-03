<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\GradeAssignment;
use App\Models\Institution;
use App\Models\StudentGrade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentGradeViewTest extends TestCase
{
    use RefreshDatabase;

    private int $institutionId;
    private Classes $class;
    private User $teacher;
    private User $student;
    private User $otherStudent;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $institution = Institution::create(['name' => 'SMK Test', 'status' => 'active']);
        $this->institutionId = $institution->id;

        AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
            'institution_id' => $this->institutionId,
        ]);

        Role::create(['name' => 'siswa']);
        Role::create(['name' => 'teacher']);

        $this->teacher = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $this->teacher->assignRole('teacher');

        $this->class = Classes::create([
            'name' => 'X RPL 1',
            'major' => 'RPL',
            'grade_level' => 'X',
            'academic_year' => '2026/2027',
            'capacity' => 30,
            'is_active' => true,
            'institution_id' => $this->institutionId,
        ]);

        $this->student = User::factory()->create([
            'institution_id' => $this->institutionId,
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);
        $this->student->assignRole('siswa');

        $this->otherStudent = User::factory()->create([
            'institution_id' => $this->institutionId,
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);
        $this->otherStudent->assignRole('siswa');

        $this->subject = Subject::create([
            'name' => 'Matematika',
            'institution_id' => $this->institutionId,
        ]);
    }

    private function makeAssignment(string $title = 'Tugas 1', ?int $score = null, ?User $for = null): GradeAssignment
    {
        $assignment = GradeAssignment::create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'title' => $title,
            'description' => 'Kerjakan dengan teliti.',
            'assigned_date' => now()->subDays(3)->toDateString(),
            // due_date dibuat relatif ke sekarang (selalu masa depan) supaya
            // test tidak bergantung tanggal kalender: kalau due_date
            // hardcoded lewat, grade unscored berubah jadi 'overdue' dan
            // badge-nya jadi 'Menunggu nilai' (bukan 'Belum dinilai').
            'due_date' => now()->addDays(7)->toDateString(),
            'max_score' => 100,
            'academic_year_id' => AcademicYear::where('is_active', true)->first()->id,
            'institution_id' => $this->institutionId,
        ]);

        StudentGrade::create([
            'grade_assignment_id' => $assignment->id,
            'student_id' => ($for ?? $this->student)->id,
            'score' => $score,
            'institution_id' => $this->institutionId,
        ]);

        return $assignment;
    }

    public function test_student_can_view_own_grades(): void
    {
        $this->makeAssignment('Ulangan Harian 1', 90);

        $response = $this->actingAs($this->student)->get(route('siswa.grades.index'));

        $response->assertOk();
        $response->assertSee('Matematika', false);
        $response->assertSee('Ulangan Harian 1', false);
        $response->assertSee('90', false);
        $response->assertSee($this->teacher->name, false);
    }

    public function test_student_does_not_see_other_students_scores(): void
    {
        $this->makeAssignment('Tugas Privat', 95, $this->otherStudent);

        $response = $this->actingAs($this->student)->get(route('siswa.grades.index'));

        $response->assertOk();
        $response->assertDontSee('Tugas Privat', false);
    }

    public function test_unscored_assignment_shows_as_pending(): void
    {
        $this->makeAssignment('Tugas Belum Dinilai', null);

        $response = $this->actingAs($this->student)->get(route('siswa.grades.index'));

        $response->assertOk();
        $response->assertSee('Tugas Belum Dinilai', false);
        $response->assertSee('Belum dinilai', false);
    }

    public function test_student_cannot_view_other_students_grade_detail(): void
    {
        $grade = $this->makeAssignment('Tugas Rahasia', 80, $this->otherStudent)->grades()->first();

        $response = $this->actingAs($this->student)->get(route('siswa.grades.show', $grade));

        $response->assertForbidden();
    }

    public function test_student_can_view_own_grade_detail(): void
    {
        $grade = $this->makeAssignment('Tugas Detail', 75)->grades()->first();

        $response = $this->actingAs($this->student)->get(route('siswa.grades.show', $grade));

        $response->assertOk();
        $response->assertSee('Tugas Detail', false);
        $response->assertSee('75', false);
    }
}
