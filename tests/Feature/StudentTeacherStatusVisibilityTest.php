<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Institution;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\TeacherStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentTeacherStatusVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private int $institutionId;

    private Classes $class;

    private User $student;

    private User $teacher;

    private User $substituteTeacher;

    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-08-10 09:00:00'));

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

        $this->teacher = User::factory()->create([
            'name' => 'Guru Utama',
            'institution_id' => $this->institutionId,
            'status' => 'active',
        ]);
        $this->teacher->assignRole('teacher');

        $this->substituteTeacher = User::factory()->create([
            'name' => 'Guru Pengganti',
            'institution_id' => $this->institutionId,
            'status' => 'active',
        ]);
        $this->substituteTeacher->assignRole('teacher');

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
            'name' => 'Siswa Test',
            'institution_id' => $this->institutionId,
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);
        $this->student->assignRole('siswa');

        $this->subject = Subject::create([
            'name' => 'Matematika',
            'institution_id' => $this->institutionId,
        ]);

        Schedule::create([
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'room' => 'R1',
            'institution_id' => $this->institutionId,
        ]);

        // Mode 'normal': jadwal 'semua' tampil setiap hari, fokus test pada status guru.
        \App\Models\Setting::set('schedule_mode', 'normal', 'general', $this->institutionId);
    }

    public function test_student_schedule_shows_only_approved_teacher_status(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'pending',
            'date' => '2026-08-10',
            'note' => 'Alasan pribadi',
            'institution_id' => $this->institutionId,
        ]);

        $this->actingAs($this->student)
            ->get(route('siswa.schedule.index', ['date' => '2026-08-10']))
            ->assertOk()
            ->assertDontSee('Guru berhalangan', false)
            ->assertDontSee('Alasan pribadi', false);

        TeacherStatus::query()->delete();
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'izin',
            'status' => 'approved',
            'date' => '2026-08-10',
            'note' => 'Alasan pribadi',
            'institution_id' => $this->institutionId,
        ]);

        $this->actingAs($this->student)
            ->get(route('siswa.schedule.index', ['date' => '2026-08-10']))
            ->assertOk()
            ->assertSee('Guru berhalangan', false)
            ->assertDontSee('Alasan pribadi', false);
    }

    public function test_student_dashboard_and_schedule_show_substitute_teacher(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'tugas_luar',
            'status' => 'approved',
            'date' => '2026-08-10',
            'substitute_teacher_id' => $this->substituteTeacher->id,
            'institution_id' => $this->institutionId,
        ]);

        $this->actingAs($this->student)
            ->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertSee('Tugas luar', false)
            ->assertSee('Digantikan Guru Pengganti', false);

        $this->actingAs($this->student)
            ->get(route('siswa.schedule.by-date', ['date' => '2026-08-10']))
            ->assertOk()
            ->assertJsonFragment([
                'label' => 'Tugas Luar',
                'substitute_teacher_name' => 'Guru Pengganti',
            ]);
    }

    public function test_student_schedule_shows_approved_teacher_sick_status(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'sakit',
            'status' => 'approved',
            'date' => '2026-08-10',
            'institution_id' => $this->institutionId,
        ]);

        $this->actingAs($this->student)
            ->get(route('siswa.schedule.index', ['date' => '2026-08-10']))
            ->assertOk()
            ->assertSee('Guru sakit', false);
    }
}
