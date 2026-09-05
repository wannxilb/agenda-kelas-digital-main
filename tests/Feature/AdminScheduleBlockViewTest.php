<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScheduleBlockViewTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected int $academicYearId;
    protected Classes $class;
    protected User $teacher;
    protected Subject $subjectGanjil;
    protected Subject $subjectGenap;
    protected Subject $subjectSemua;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = User::where('email', 'admin@school.com')->first();
        $this->academicYearId = AcademicYear::first()->id;

        $this->class = Classes::create([
            'name' => 'X RPL TEST',
            'major' => 'RPL',
            'grade_level' => 'X',
            'academic_year' => AcademicYear::first()->name,
            'capacity' => 30,
            'is_active' => true,
            'institution_id' => $this->admin->institution_id,
        ]);

        $this->teacher = User::factory()->create([
            'institution_id' => $this->admin->institution_id,
            'status' => 'active',
        ]);

        $this->subjectGanjil = Subject::create(['name' => 'Mapel Ganjil Admin', 'institution_id' => $this->admin->institution_id]);
        $this->subjectGenap = Subject::create(['name' => 'Mapel Genap Admin', 'institution_id' => $this->admin->institution_id]);
        $this->subjectSemua = Subject::create(['name' => 'Mapel Semua Admin', 'institution_id' => $this->admin->institution_id]);
    }

    private function makeSchedule(string $subjectId, string $weekType): Schedule
    {
        return Schedule::create([
            'class_id' => $this->class->id,
            'subject_id' => $subjectId,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'week_type' => $weekType,
            'start_time' => '08:00:00',
            'end_time' => '09:30:00',
            'academic_year_id' => $this->academicYearId,
            'institution_id' => $this->admin->institution_id,
        ]);
    }

    /**
     * Mode block: tabel & kalender admin menampilkan jadwal 'ganjil' DAN 'genap'
     * secara terpisah (dua bagian bertumpuk), sementara jadwal 'semua' tidak tampil.
     */
    public function test_block_mode_shows_ganjil_and_genap_separated(): void
    {
        Setting::set('schedule_mode', 'block', 'general', $this->admin->institution_id);

        $this->makeSchedule($this->subjectGanjil->id, 'ganjil');
        $this->makeSchedule($this->subjectGenap->id, 'genap');
        $this->makeSchedule($this->subjectSemua->id, 'semua');

        $response = $this->actingAs($this->admin)->get(route('admin.schedules.index'));

        $response->assertOk();
        $response->assertSee('Mapel Ganjil Admin');
        $response->assertSee('Mapel Genap Admin');
        $response->assertSee('Minggu Ganjil', false);
        $response->assertSee('Minggu Genap', false);
        $response->assertDontSee('Mapel Semua Admin');
    }

    /**
     * Mode normal: hanya jadwal 'semua' yang tampil, tanpa bagian ganjil/genap.
     */
    public function test_normal_mode_shows_semua_only(): void
    {
        Setting::set('schedule_mode', 'normal', 'general', $this->admin->institution_id);

        $this->makeSchedule($this->subjectGanjil->id, 'ganjil');
        $this->makeSchedule($this->subjectGenap->id, 'genap');
        $this->makeSchedule($this->subjectSemua->id, 'semua');

        $response = $this->actingAs($this->admin)->get(route('admin.schedules.index'));

        $response->assertOk();
        $response->assertSee('Mapel Semua Admin');
        $response->assertDontSee('Mapel Ganjil Admin');
        $response->assertDontSee('Mapel Genap Admin');
        $response->assertDontSee('Minggu Ganjil', false);
        $response->assertDontSee('Minggu Genap', false);
    }
}