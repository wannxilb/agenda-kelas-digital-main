<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Institution;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentScheduleWeekFilterTest extends TestCase
{
    use RefreshDatabase;

    private int $institutionId;
    private Classes $class;
    private User $student;
    private Subject $subjectGanjil;
    private Subject $subjectGenap;
    private int $activeYearId;

    protected function setUp(): void
    {
        parent::setUp();

        $institution = Institution::create(['name' => 'SMK Test', 'status' => 'active']);
        $this->institutionId = $institution->id;

        $year = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
            'institution_id' => $this->institutionId,
        ]);
        $this->activeYearId = $year->id;

        Role::create(['name' => 'siswa']);

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

        $this->subjectGanjil = Subject::create(['name' => 'Matematika Ganjil', 'institution_id' => $this->institutionId]);
        $this->subjectGenap = Subject::create(['name' => 'Fisika Genap', 'institution_id' => $this->institutionId]);
    }

    private function makeSchedule(string $subjectId, string $weekType, string $day = 'Monday'): Schedule
    {
        return Schedule::create([
            'class_id' => $this->class->id,
            'subject_id' => $subjectId,
            'teacher_id' => $this->teacherFixture()->id,
            'day' => $day,
            'week_type' => $weekType,
            'start_time' => '08:00:00',
            'end_time' => '09:30:00',
            'academic_year_id' => $this->activeYearId,
            'institution_id' => $this->institutionId,
        ]);
    }

    private ?User $teacherFixture = null;
    private function teacherFixture(): User
    {
        return $this->teacherFixture ??= User::factory()->create([
            'institution_id' => $this->institutionId,
            'status' => 'active',
        ]);
    }

    /**
     * Pada minggu ganjil (hari ke-1 s.d. 7 dalam bulan), jadwal 'semua' + 'ganjil'
     * tampil, sedangkan jadwal 'genap' TIDAK tampil.
     */
    public function test_odd_week_shows_semua_and_ganjil_only(): void
    {
        $this->withoutExceptionHandling();
        // create 'semua' Monday schedule
        $semua = Subject::create(['name' => 'Semua Minggu Mapel', 'institution_id' => $this->institutionId]);
        $this->makeSchedule($semua->id, 'semua');
        $this->makeSchedule($this->subjectGanjil->id, 'ganjil');
        $this->makeSchedule($this->subjectGenap->id, 'genap');

        // tanggal dengan hari ke-1 (minggu ke-1 = ganjil), pastikan itu Monday? Tak apa,
        // view menampilkan seluruh minggu. Kita cek kolom Monday.
        $date = Carbon::parse('2026-09-01'); // 1 Sept 2026, minggu ke-1 = ganjil

        $response = $this->actingAs($this->student)
            ->get(route('siswa.schedule.index', ['date' => $date->format('Y-m-d')]));

        $response->assertOk();
        $response->assertSee('Semua Minggu Mapel');
        $response->assertSee('Matematika Ganjil');
        $response->assertDontSee('Fisika Genap');
    }

    /**
     * Pada minggu genap (hari ke-8 s.d. 14 dalam bulan), jadwal 'semua' + 'genap'
     * tampil, sedangkan jadwal 'ganjil' TIDAK tampil.
     */
    public function test_even_week_shows_semua_and_genap_only(): void
    {
        $this->withoutExceptionHandling();
        $semua = Subject::create(['name' => 'Semua Minggu Mapel', 'institution_id' => $this->institutionId]);
        $this->makeSchedule($semua->id, 'semua');
        $this->makeSchedule($this->subjectGanjil->id, 'ganjil');
        $this->makeSchedule($this->subjectGenap->id, 'genap');

        $date = Carbon::parse('2026-09-08'); // 8 Sept 2026, minggu ke-2 = genap

        $response = $this->actingAs($this->student)
            ->get(route('siswa.schedule.index', ['date' => $date->format('Y-m-d')]));

        $response->assertOk();
        $response->assertSee('Semua Minggu Mapel');
        $response->assertSee('Fisika Genap');
        $response->assertDontSee('Matematika Ganjil');
    }

    /**
     * Header harus menampilkan label minggu yang benar (Minggu Ganjil / Minggu Genap).
     */
    public function test_week_type_label_is_rendered_in_header(): void
    {
        $dateGanjil = Carbon::parse('2026-09-03'); // minggu ganjil
        $response = $this->actingAs($this->student)
            ->get(route('siswa.schedule.index', ['date' => $dateGanjil->format('Y-m-d')]));
        $response->assertOk();
        $response->assertSee('Minggu Ganjil', false);

        $dateGenap = Carbon::parse('2026-09-10'); // minggu genap
        $response = $this->actingAs($this->student)
            ->get(route('siswa.schedule.index', ['date' => $dateGenap->format('Y-m-d')]));
        $response->assertOk();
        $response->assertSee('Minggu Genap', false);
    }

    /**
     * Endpoint byDate harus memfilter jadwal sesuai minggu dari tanggal yang diminta.
     */
    public function test_bydate_filters_by_week(): void
    {
        $ganjil = $this->makeSchedule($this->subjectGanjil->id, 'ganjil');
        $genap = $this->makeSchedule($this->subjectGenap->id, 'genap');

        // 7 Sept 2026 = Senin, minggu ke-1 = ganjil
        $response = $this->actingAs($this->student)
            ->getJson(route('siswa.schedule.by-date', ['date' => '2026-09-07']));
        $response->assertOk()->assertJsonCount(1);
        $response->assertJsonPath('0.subject_name', 'Matematika Ganjil');

        // 14 Sept 2026 = Senin, minggu ke-2 = genap
        $response = $this->actingAs($this->student)
            ->getJson(route('siswa.schedule.by-date', ['date' => '2026-09-14']));
        $response->assertOk()->assertJsonCount(1);
        $response->assertJsonPath('0.subject_name', 'Fisika Genap');
    }

    /**
     * Mode 'normal': semua jadwal (semua + ganjil + genap) tampil tanpa peduli minggu.
     */
    public function test_normal_mode_shows_all_schedules_any_week(): void
    {
        \App\Models\Setting::set('schedule_mode', 'normal', 'general', $this->institutionId);

        $this->makeSchedule($this->subjectGanjil->id, 'ganjil');
        $this->makeSchedule($this->subjectGenap->id, 'genap');

        // minggu genap (hari ke-9), tapi karena mode normal semua tetap tampil
        $date = Carbon::parse('2026-09-09');

        $response = $this->actingAs($this->student)
            ->get(route('siswa.schedule.index', ['date' => $date->format('Y-m-d')]));

        $response->assertOk();
        $response->assertSee('Matematika Ganjil');
        $response->assertSee('Fisika Genap');
    }

    /**
     * Mode 'block' default: jadwal 'ganjil' TIDAK tampil di minggu genap.
     */
    public function test_block_mode_still_filters(): void
    {
        \App\Models\Setting::set('schedule_mode', 'block', 'general', $this->institutionId);

        $this->makeSchedule($this->subjectGanjil->id, 'ganjil');
        $this->makeSchedule($this->subjectGenap->id, 'genap');

        $date = Carbon::parse('2026-09-09'); // minggu genap

        $response = $this->actingAs($this->student)
            ->get(route('siswa.schedule.index', ['date' => $date->format('Y-m-d')]));

        $response->assertOk();
        $response->assertDontSee('Matematika Ganjil');
        $response->assertSee('Fisika Genap');
    }
}
