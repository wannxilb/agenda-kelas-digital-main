<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Agenda;
use App\Models\ClassHistory;
use App\Models\Classes;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SekretarisReadOnlyGuardTest extends TestCase
{
    use RefreshDatabase;

    protected User $sekretaris;
    protected Classes $currentClass;
    protected Classes $pastClass;
    protected User $guru;
    protected Subject $subject;
    protected AcademicYear $activeYear;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-05-18 09:00:00')); // Monday
        $this->seed();

        // Izin operasional + lokasi agar middleware tidak menghalangi test
        Setting::set('operational_override_until', '2099-12-31 23:59:59', 'general', 1);
        Setting::set('school_location_enabled', '0', 'general', 1);

        $classIds = Classes::pluck('id')->all();
        $this->currentClass = Classes::find($classIds[0]);
        $this->pastClass = Classes::find($classIds[1]);

        $this->sekretaris = User::factory()->create([
            'class_id' => $this->currentClass->id,
            'institution_id' => 1,
        ]);
        $this->sekretaris->assignRole('sekretaris');

        $this->activeYear = AcademicYear::where('is_active', true)->firstOrFail();
        ClassHistory::create([
            'user_id' => $this->sekretaris->id,
            'class_id' => $this->pastClass->id,
            'academic_year_id' => $this->activeYear->id,
            'institution_id' => 1,
        ]);

        $this->guru = User::where('email', 'guru@school.com')->firstOrFail();
        $this->subject = Subject::first();
    }

    private function makeAgenda(Classes $class): Agenda
    {
        return Agenda::create([
            'class_id' => $class->id,
            'teacher_id' => $this->guru->id,
            'subject_id' => $this->subject->id,
            'room' => 'Ruang 1',
            'date' => '2026-05-18',
            'title' => 'Agenda Uji Sekretaris',
            'description' => 'Deskripsi uji',
            'status' => 'published',
            'academic_year_id' => $this->activeYear->id,
            'institution_id' => 1,
        ]);
    }

    public function test_sekretaris_can_view_past_class_agenda_index(): void
    {
        $response = $this->actingAs($this->sekretaris)
            ->get('/sekretaris/agenda?class_id=' . $this->pastClass->id);

        $response->assertOk()
            ->assertSee('Mode lihat');
    }

    public function test_sekretaris_cannot_open_create_agenda_for_past_class(): void
    {
        $this->actingAs($this->sekretaris)
            ->get('/sekretaris/agenda/create?class_id=' . $this->pastClass->id)
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_sekretaris_cannot_store_agenda_for_past_class(): void
    {
        $this->actingAs($this->sekretaris)
            ->post('/sekretaris/agenda', [
                'class_id' => $this->pastClass->id,
                'title' => 'Agenda Tidak Boleh',
                'teacher_id' => $this->guru->id,
                'room' => 'Ruang 1',
                'date' => '2026-05-18',
                'description' => 'test',
                'status' => 'published',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('agendas', ['title' => 'Agenda Tidak Boleh']);
    }

    public function test_sekretaris_cannot_edit_agenda_of_past_class(): void
    {
        $agenda = $this->makeAgenda($this->pastClass);

        $this->actingAs($this->sekretaris)
            ->get('/sekretaris/agenda/' . $agenda->id . '/edit')
            ->assertForbidden();
    }

    public function test_sekretaris_cannot_delete_agenda_of_past_class(): void
    {
        $agenda = $this->makeAgenda($this->pastClass);

        $this->actingAs($this->sekretaris)
            ->delete('/sekretaris/agenda/' . $agenda->id)
            ->assertForbidden();

        $this->assertDatabaseHas('agendas', ['id' => $agenda->id]);
    }

    public function test_sekretaris_can_edit_agenda_of_current_class(): void
    {
        $agenda = $this->makeAgenda($this->currentClass);

        $this->actingAs($this->sekretaris)
            ->get('/sekretaris/agenda/' . $agenda->id . '/edit')
            ->assertOk();
    }

    public function test_sekretaris_attendance_index_redirects_past_class_to_report(): void
    {
        $this->actingAs($this->sekretaris)
            ->get('/sekretaris/attendance?class_id=' . $this->pastClass->id)
            ->assertRedirect(route('sekretaris.attendance.report', ['class_id' => $this->pastClass->id]));

        $this->actingAs($this->sekretaris)
            ->get('/sekretaris/attendance')
            ->assertOk();
    }

    public function test_sekretaris_cannot_store_attendance_for_past_class(): void
    {
        $this->actingAs($this->sekretaris)
            ->post('/sekretaris/attendance/store', [
                'class_id' => $this->pastClass->id,
                'date' => '2026-05-18',
                'attendance' => [],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }
}