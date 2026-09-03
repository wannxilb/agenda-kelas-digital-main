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

class WaliKelasGradeViewTest extends TestCase
{
    use RefreshDatabase;

    private int $institutionId;
    private Classes $class;
    private User $waliKelas;
    private User $teacher;
    private User $student;
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $reflection = new \ReflectionClass(\App\Http\Controllers\WaliKelas\GradeController::class);
        $property = $reflection->getProperty('cachedActiveAcademicYear');
        $property->setValue(null, null);

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
        Role::create(['name' => 'wali_kelas']);

        $this->waliKelas = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $this->waliKelas->assignRole('wali_kelas');

        $this->teacher = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $this->teacher->assignRole('teacher');

        $this->class = Classes::create([
            'name' => 'X RPL 1',
            'major' => 'RPL',
            'grade_level' => 'X',
            'academic_year' => '2026/2027',
            'capacity' => 30,
            'is_active' => true,
            'homeroom_teacher_id' => $this->waliKelas->id,
            'institution_id' => $this->institutionId,
        ]);

        $this->student = User::factory()->create([
            'institution_id' => $this->institutionId,
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);
        $this->student->assignRole('siswa');

        $this->subject = Subject::create([
            'name' => 'Matematika',
            'institution_id' => $this->institutionId,
        ]);
    }

    private function makeAssignment(string $title = 'Tugas 1', ?int $score = null): GradeAssignment
    {
        $assignment = GradeAssignment::create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'title' => $title,
            'description' => 'Kerjakan dengan teliti.',
            'assigned_date' => '2026-08-01',
            'due_date' => '2026-08-10',
            'max_score' => 100,
            'academic_year_id' => AcademicYear::where('is_active', true)->first()->id,
            'institution_id' => $this->institutionId,
        ]);

        StudentGrade::create([
            'grade_assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'score' => $score,
            'institution_id' => $this->institutionId,
        ]);

        return $assignment;
    }

    public function test_wali_kelas_can_view_student_grades_index(): void
    {
        $this->makeAssignment('Ulangan Harian 1', 88);

        $response = $this->actingAs($this->waliKelas)->get(route('wali-kelas.grades.index'));

        $response->assertOk();
        $response->assertSee('Nilai Siswa', false);
        $response->assertSee('Matematika', false);
        $response->assertSee($this->student->name, false);
        $response->assertSee('88', false);
    }

    public function test_wali_kelas_without_homeroom_class_sees_empty_state(): void
    {
        $otherWali = User::factory()->create(['institution_id' => $this->institutionId, 'status' => 'active']);
        $otherWali->assignRole('wali_kelas');

        $response = $this->actingAs($otherWali)->get(route('wali-kelas.grades.index'));

        $response->assertOk();
        $response->assertSee('Belum ada kelas', false);
    }

    public function test_wali_kelas_sees_grades_from_all_subjects(): void
    {
        $this->makeAssignment('Ulangan Harian 1', 88);

        $otherSubject = Subject::create(['name' => 'Bahasa Indonesia', 'institution_id' => $this->institutionId]);

        $assignment = GradeAssignment::create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->class->id,
            'subject_id' => $otherSubject->id,
            'title' => 'Tugas Menulis',
            'description' => 'Tulis karangan.',
            'assigned_date' => '2026-08-02',
            'due_date' => '2026-08-12',
            'max_score' => 100,
            'academic_year_id' => AcademicYear::where('is_active', true)->first()->id,
            'institution_id' => $this->institutionId,
        ]);

        StudentGrade::create([
            'grade_assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'score' => 92,
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->waliKelas)->get(route('wali-kelas.grades.index'));

        $response->assertOk();
        $response->assertSee('Matematika', false);
        $response->assertSee('Bahasa Indonesia', false);
    }

    public function test_wali_kelas_can_filter_grades_by_subject(): void
    {
        $this->makeAssignment('Ulangan Harian 1', 88);

        $response = $this->actingAs($this->waliKelas)->get(route('wali-kelas.grades.index', [
            'subject_id' => $this->subject->id,
        ]));

        $response->assertOk();
        $response->assertSee('Matematika', false);
        $response->assertSee($this->student->name, false);
    }

    public function test_wali_kelas_can_search_students_in_grades_case_insensitively(): void
    {
        $this->student->update([
            'name' => 'Budi Nilai Wali',
            'nis' => 'WK-NILAI-001',
        ]);

        $otherStudent = User::factory()->create([
            'name' => 'Andi Nilai Pembanding',
            'nis' => 'WK-NILAI-002',
            'institution_id' => $this->institutionId,
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);
        $otherStudent->assignRole('siswa');

        $this->makeAssignment('Ulangan Harian 1', 88);

        $response = $this->actingAs($this->waliKelas)->get(route('wali-kelas.grades.index', [
            'search' => 'BUDI',
        ]));

        $response->assertOk();
        $response->assertSee('Budi Nilai Wali', false);
        $response->assertDontSee('Andi Nilai Pembanding', false);
    }

    public function test_subject_filter_keeps_all_subject_options_available(): void
    {
        $this->makeAssignment('Ulangan Harian 1', 88);

        $otherSubject = Subject::create(['name' => 'Bahasa Indonesia', 'institution_id' => $this->institutionId]);
        $assignment = GradeAssignment::create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->class->id,
            'subject_id' => $otherSubject->id,
            'title' => 'Tugas Menulis',
            'description' => 'Tulis karangan.',
            'assigned_date' => '2026-08-02',
            'due_date' => '2026-08-12',
            'max_score' => 100,
            'academic_year_id' => AcademicYear::where('is_active', true)->first()->id,
            'institution_id' => $this->institutionId,
        ]);

        StudentGrade::create([
            'grade_assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'score' => 92,
            'institution_id' => $this->institutionId,
        ]);

        $response = $this->actingAs($this->waliKelas)->get(route('wali-kelas.grades.index', [
            'subject_id' => $this->subject->id,
        ]));

        $response->assertOk();
        $response->assertSee('Matematika', false);
        $response->assertSee('Bahasa Indonesia', false);
    }

    public function test_other_roles_cannot_access_wali_kelas_grades(): void
    {
        $student = User::factory()->create([
            'institution_id' => $this->institutionId,
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);
        $student->assignRole('siswa');

        $response = $this->actingAs($student)->get(route('wali-kelas.grades.index'));

        $response->assertForbidden();
    }
}
