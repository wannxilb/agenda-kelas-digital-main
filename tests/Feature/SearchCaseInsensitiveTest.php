<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\TeacherStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchCaseInsensitiveTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected Classes $class;
    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->teacher = User::where('email', 'guru@school.com')->first();
        $this->class = Classes::first();
        $this->subject = Subject::first();
    }

    public function test_guru_agenda_search_is_case_insensitive(): void
    {
        $agenda = Agenda::create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'date' => '2026-07-23',
            'room' => 'Ruang Test',
            'title' => 'Rapat Kurikulum Merdeka',
            'description' => 'Membahas capaian pembelajaran',
            'status' => 'published',
        ]);

        // Pencarian huruf kecil harus ketemu
        $this->actingAs($this->teacher)
            ->get(route('guru.agenda.index', ['search' => 'rapat']))
            ->assertOk()
            ->assertSee('Rapat Kurikulum Merdeka');

        // Pencarian huruf besar harus ketemu juga (sebelumnya case-sensitive di PostgreSQL)
        $this->actingAs($this->teacher)
            ->get(route('guru.agenda.index', ['search' => 'RAPAT']))
            ->assertOk()
            ->assertSee('Rapat Kurikulum Merdeka');

        // Term yang tidak cocok tidak muncul
        $this->actingAs($this->teacher)
            ->get(route('guru.agenda.index', ['search' => 'tidaktidakada']))
            ->assertOk()
            ->assertDontSee('Rapat Kurikulum Merdeka');
    }

    public function test_teacher_status_search_is_case_insensitive_and_date_search_works(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'date' => '2026-07-23',
            'type' => 'izin',
            'status' => 'pending',
            'note' => 'Rapat koordinasi guru',
            'academic_year_id' => AcademicYear::first()->id,
            'institution_id' => $this->teacher->institution_id,
        ]);

        // Search note dengan case berbeda
        $this->actingAs($this->teacher)
            ->get(route('guru.teacher-status.index', ['search' => 'RAPAT']))
            ->assertOk()
            ->assertSee('Rapat koordinasi guru');

        // Search tanggal (kolom date) tidak boleh error 500 di PostgreSQL
        $this->actingAs($this->teacher)
            ->get(route('guru.teacher-status.index', ['search' => '2026-07']))
            ->assertOk()
            ->assertSee('Rapat koordinasi guru');
    }

    public function test_sekretaris_attendance_report_search_is_case_insensitive(): void
    {
        $this->withoutMiddleware();

        $sekretaris = User::factory()->create([
            'class_id' => $this->class->id,
            'institution_id' => 1,
            'status' => 'active',
        ]);
        $sekretaris->assignRole('sekretaris');

        $matchingStudent = User::factory()->create([
            'name' => 'Budi Santoso Unik',
            'nis' => 'SISWA-CASE-001',
            'class_id' => $this->class->id,
            'institution_id' => 1,
            'status' => 'active',
        ]);
        $matchingStudent->assignRole('siswa');

        $otherStudent = User::factory()->create([
            'name' => 'Andi Pembanding Unik',
            'nis' => 'SISWA-CASE-002',
            'class_id' => $this->class->id,
            'institution_id' => 1,
            'status' => 'active',
        ]);
        $otherStudent->assignRole('siswa');

        $this->actingAs($sekretaris)
            ->get(route('sekretaris.attendance.report', ['search' => 'budi']))
            ->assertOk()
            ->assertSeeText('Budi Santoso Unik')
            ->assertDontSeeText('Andi Pembanding Unik');

        $this->actingAs($sekretaris)
            ->get(route('sekretaris.attendance.report', ['search' => 'BUDI']))
            ->assertOk()
            ->assertSeeText('Budi Santoso Unik')
            ->assertDontSeeText('Andi Pembanding Unik');
    }
}
