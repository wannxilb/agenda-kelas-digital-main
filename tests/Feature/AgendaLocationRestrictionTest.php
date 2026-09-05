<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckOperationalHours;
use App\Models\Classes;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\TeacherStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaLocationRestrictionTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;

    protected Classes $class;

    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-05-18 09:00:00'));
        $this->withoutMiddleware(CheckOperationalHours::class);
        $this->seed();

        $this->teacher = User::where('email', 'guru@school.com')->first();
        $this->class = Classes::first();
        $this->subject = Subject::first();

        Schedule::create([
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'room' => 'Lab RPL 1',
            'institution_id' => $this->teacher->institution_id,
        ]);

        Setting::set('agenda_location_enabled', '1', 'general', $this->teacher->institution_id);
        Setting::set('agenda_location_latitude', '-6.200000', 'general', $this->teacher->institution_id);
        Setting::set('agenda_location_longitude', '106.816666', 'general', $this->teacher->institution_id);
        Setting::set('agenda_location_radius_meters', '100', 'general', $this->teacher->institution_id);

        // Mode 'normal': jadwal 'semua' berlaku setiap hari, fokus test pada lokasi agenda.
        Setting::set('schedule_mode', 'normal', 'general', $this->teacher->institution_id);
    }

    public function test_agenda_store_requires_location_when_restriction_enabled(): void
    {
        $response = $this->actingAs($this->teacher)->post(route('guru.agenda.store'), $this->agendaPayload());

        $response->assertSessionHas('error', 'Lokasi wajib diaktifkan untuk mengisi agenda.');
        $this->assertDatabaseMissing('agendas', ['title' => 'Jurnal Lokasi']);
    }

    public function test_agenda_store_rejects_location_outside_allowed_area(): void
    {
        $response = $this->actingAs($this->teacher)->post(route('guru.agenda.store'), $this->agendaPayload([
            'agenda_latitude' => '-6.300000',
            'agenda_longitude' => '106.816666',
        ]));

        $response->assertSessionHas('error', 'Agenda hanya dapat diisi dari wilayah yang sudah ditentukan Admin.');
        $this->assertDatabaseMissing('agendas', ['title' => 'Jurnal Lokasi']);
    }

    public function test_agenda_store_accepts_location_inside_allowed_area(): void
    {
        $response = $this->actingAs($this->teacher)->post(route('guru.agenda.store'), $this->agendaPayload([
            'agenda_latitude' => '-6.200100',
            'agenda_longitude' => '106.816666',
        ]));

        $response->assertRedirect(route('guru.agenda.index'));
        $this->assertDatabaseHas('agendas', ['title' => 'Jurnal Lokasi']);
    }

    public function test_approved_teacher_sick_status_allows_agenda_outside_allowed_area(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'sakit',
            'status' => 'approved',
            'date' => '2026-05-18',
            'institution_id' => $this->teacher->institution_id,
        ]);

        $response = $this->actingAs($this->teacher)->post(route('guru.agenda.store'), $this->agendaPayload([
            'agenda_latitude' => '-6.300000',
            'agenda_longitude' => '106.816666',
            'title' => 'Jurnal Remote Sakit',
        ]));

        $response->assertRedirect(route('guru.agenda.index'));
        $this->assertDatabaseHas('agendas', ['title' => 'Jurnal Remote Sakit']);
    }

    public function test_rejected_teacher_sick_status_does_not_allow_agenda_outside_allowed_area(): void
    {
        TeacherStatus::create([
            'teacher_id' => $this->teacher->id,
            'type' => 'sakit',
            'status' => 'rejected',
            'date' => '2026-05-18',
            'institution_id' => $this->teacher->institution_id,
        ]);

        $response = $this->actingAs($this->teacher)->post(route('guru.agenda.store'), $this->agendaPayload([
            'agenda_latitude' => '-6.300000',
            'agenda_longitude' => '106.816666',
            'title' => 'Jurnal Rejected Sakit',
        ]));

        $response->assertSessionHas('error', 'Agenda hanya dapat diisi dari wilayah yang sudah ditentukan Admin.');
        $this->assertDatabaseMissing('agendas', ['title' => 'Jurnal Rejected Sakit']);
    }

    private function agendaPayload(array $overrides = []): array
    {
        return array_merge([
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'room' => 'Lab RPL 1',
            'date' => '2026-05-18',
            'title' => 'Jurnal Lokasi',
            'description' => 'Catatan kegiatan belajar.',
            'status' => 'published',
        ], $overrides);
    }
}
