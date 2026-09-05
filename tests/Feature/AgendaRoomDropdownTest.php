<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Agenda;
use App\Models\Setting;
use Carbon\Carbon;

class AgendaRoomDropdownTest extends TestCase
{
    use RefreshDatabase;

    protected $sekretaris;
    protected $guru;
    protected $class;
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        Carbon::setTestNow(Carbon::parse('2026-05-18 09:00:00')); // Monday, during the seeded schedule
        $this->seed();

        $this->class = Classes::first();
        
        $this->sekretaris = User::factory()->create([
            'class_id' => $this->class->id
        ]);
        $this->sekretaris->assignRole('sekretaris');

        $this->guru = User::where('email', 'guru@school.com')->first();
        
        $this->subject = Subject::first();

        // Create a schedule for today
        Schedule::create([
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->guru->id,
            'day' => Carbon::today()->format('l'),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'room' => 'Lab RPL 1'
        ]);

        // Mode 'normal': jadwal 'semua' berlaku setiap hari, fokus test pada dropdown ruang.
        Setting::set('schedule_mode', 'normal', 'general');
    }

    /**
     * Test Sekretaris get-schedule-info API.
     */
    public function test_sekretaris_can_get_schedule_info(): void
    {
        $date = Carbon::today()->format('Y-m-d');
        
        $response = $this->actingAs($this->sekretaris)
            ->getJson("/sekretaris/agenda/get-schedule-info?date={$date}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'has_schedule',
                'day',
                'subjects',
                'rooms'
            ])
            ->assertJsonFragment([
                'has_schedule' => true,
            ]);
            
        $this->assertContains('Lab RPL 1', $response->json('rooms'));
    }

    /**
     * Test Guru get-schedule-info API.
     */
    public function test_guru_can_get_schedule_info(): void
    {
        $date = Carbon::today()->format('Y-m-d');
        
        $response = $this->actingAs($this->guru)
            ->getJson("/guru/agenda/get-schedule-info?class_id={$this->class->id}&date={$date}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'has_schedule' => true,
            ]);

        $this->assertContains('Lab RPL 1', $response->json('rooms'));
    }

    /**
     * Test Agenda storage with room field.
     */
    public function test_can_store_agenda_with_room(): void
    {
        $this->withoutExceptionHandling();
        $date = Carbon::today()->format('Y-m-d');
        
        $response = $this->actingAs($this->sekretaris)
            ->postJson('/sekretaris/agenda', [
                'title' => 'Test Agenda',
                'subject_id' => $this->subject->id,
                'teacher_id' => $this->guru->id,
                'room' => 'Lab RPL 1',
                'date' => $date,
                'description' => 'Test description',
                'status' => 'published'
            ]);

        $response->assertRedirect('/sekretaris/agenda');
        
        $this->assertDatabaseHas('agendas', [
            'title' => 'Test Agenda',
            'room' => 'Lab RPL 1',
            'class_id' => $this->class->id
        ]);
    }

    /**
     * Test fallback when no schedule exists.
     */
    public function test_fallback_when_no_schedule(): void
    {
        // Sunday usually has no schedule
        $date = Carbon::today()->next(Carbon::SUNDAY)->format('Y-m-d');
        
        $response = $this->actingAs($this->sekretaris)
            ->getJson("/sekretaris/agenda/get-schedule-info?date={$date}");

        $response->assertStatus(200)
            ->assertJson([
                'has_schedule' => false
            ]);
            
        // Should return all rooms previously used by this class
        $this->assertNotNull($response->json('rooms'));
    }
}
