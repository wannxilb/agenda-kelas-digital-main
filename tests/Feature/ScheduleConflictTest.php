<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Room;

class ScheduleConflictTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $teacher;
    protected $class1;
    protected $class2;
    protected $subject1;
    protected $subject2;
    protected $room1;
    protected $room2;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default roles, permissions, users, and classes
        $this->seed();

        $this->admin = User::where('email', 'admin@school.com')->first();
        $this->teacher = User::where('email', 'guru@school.com')->first();
        $this->class1 = Classes::where('name', 'XI RPL 1')->first();
        $this->class2 = Classes::where('name', 'XI RPL 2')->first();
        
        // Let's find or create subjects
        $this->subject1 = Subject::where('name', 'Pemrograman Web')->first();
        $this->subject2 = Subject::where('name', 'Basis Data')->first();

        // Clear default seeded schedules to start clean
        Schedule::truncate();

        $this->room1 = Room::create(['name' => 'Lab 101', 'type' => 'laboratorium', 'capacity' => 36, 'is_active' => true]);
        $this->room2 = Room::create(['name' => 'Lab 102', 'type' => 'laboratorium', 'capacity' => 36, 'is_active' => true]);
    }

    /**
     * Test successful creation of schedule when no conflicts.
     */
    public function test_can_create_schedule_when_no_conflicts(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/schedules', [
                'class_id' => $this->class1->id,
                'subject_id' => $this->subject1->id,
                'teacher_id' => $this->teacher->id,
                'day' => 'Monday',
                'week_type' => 'semua',
                'start_time' => '08:00',
                'end_time' => '09:30',
                'room_id' => $this->room1->id,
            ]);

        $response->assertRedirect('/admin/schedules');
        $response->assertSessionHas('success', 'Jadwal berhasil ditambahkan!');
        
        $this->assertDatabaseHas('schedules', [
            'class_id' => $this->class1->id,
            'day' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:30',
            'room_id' => $this->room1->id,
        ]);
    }

    /**
     * Test class conflict validation.
     */
    public function test_fails_when_class_has_overlapping_schedule(): void
    {
        // Create an existing schedule
        Schedule::create([
            'class_id' => $this->class1->id,
            'subject_id' => $this->subject1->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'week_type' => 'semua',
            'start_time' => '08:00:00',
            'end_time' => '09:30:00',
            'room_id' => $this->room1->id,
        ]);

        // Attempt to create another schedule for the same class at overlapping time (08:30 - 10:00)
        $response = $this->actingAs($this->admin)
            ->from('/admin/schedules/create')
            ->post('/admin/schedules', [
                'class_id' => $this->class1->id,
                'subject_id' => $this->subject2->id,
                'teacher_id' => $this->teacher->id,
                'day' => 'Monday',
                'week_type' => 'semua',
                'start_time' => '08:30',
                'end_time' => '10:00',
                'room_id' => $this->room2->id,
            ]);

        $response->assertRedirect('/admin/schedules/create');
        $response->assertSessionHas('error', 'Jadwal bentrok dengan jadwal lain di kelas yang sama!');
        
        // Assert count remains 1
        $this->assertEquals(1, Schedule::count());
    }

    /**
     * Test teacher conflict validation.
     */
    public function test_fails_when_teacher_is_busy_elsewhere(): void
    {
        // Create an existing schedule for the teacher in Class 1
        Schedule::create([
            'class_id' => $this->class1->id,
            'subject_id' => $this->subject1->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'week_type' => 'semua',
            'start_time' => '08:00:00',
            'end_time' => '09:30:00',
            'room_id' => $this->room1->id,
        ]);

        // Attempt to assign the same teacher to Class 2 at an overlapping time (09:00 - 10:30)
        $response = $this->actingAs($this->admin)
            ->from('/admin/schedules/create')
            ->post('/admin/schedules', [
                'class_id' => $this->class2->id,
                'subject_id' => $this->subject2->id,
                'teacher_id' => $this->teacher->id,
                'day' => 'Monday',
                'week_type' => 'semua',
                'start_time' => '09:00',
                'end_time' => '10:30',
                'room_id' => $this->room2->id,
            ]);

        $response->assertRedirect('/admin/schedules/create');
        $response->assertSessionHas('error', 'Guru yang bersangkutan sudah memiliki jadwal mengajar di jam yang sama!');
        
        // Assert count remains 1
        $this->assertEquals(1, Schedule::count());
    }

    /**
     * Test room conflict validation.
     */
    public function test_fails_when_room_is_occupied(): void
    {
        // Create an existing schedule in Lab 101
        Schedule::create([
            'class_id' => $this->class1->id,
            'subject_id' => $this->subject1->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'week_type' => 'semua',
            'start_time' => '08:00:00',
            'end_time' => '09:30:00',
            'room_id' => $this->room1->id,
        ]);

        // Create another teacher to avoid teacher conflict
        $anotherTeacher = User::factory()->create();
        $anotherTeacher->assignRole('teacher');

        // Attempt to create a schedule for Class 2 in the same room (Lab 101) at overlapping time (09:00 - 10:30)
        $response = $this->actingAs($this->admin)
            ->from('/admin/schedules/create')
            ->post('/admin/schedules', [
                'class_id' => $this->class2->id,
                'subject_id' => $this->subject2->id,
                'teacher_id' => $anotherTeacher->id,
                'day' => 'Monday',
                'week_type' => 'semua',
                'start_time' => '09:00',
                'end_time' => '10:30',
                'room_id' => $this->room1->id,
            ]);

        $response->assertRedirect('/admin/schedules/create');
        $response->assertSessionHas('error', 'Ruangan sudah digunakan untuk jadwal lain pada jam yang sama!');
        
        // Assert count remains 1
        $this->assertEquals(1, Schedule::count());
    }

    /**
     * Test back-to-back schedules (which should be allowed, no boundary overlap conflict).
     */
    public function test_allows_back_to_back_schedules(): void
    {
        // Create schedule 1 (08:00 - 09:30)
        Schedule::create([
            'class_id' => $this->class1->id,
            'subject_id' => $this->subject1->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'week_type' => 'semua',
            'start_time' => '08:00:00',
            'end_time' => '09:30:00',
            'room_id' => $this->room1->id,
        ]);

        // Attempt to create schedule 2 (09:30 - 11:00) with same teacher and room (back to back)
        $response = $this->actingAs($this->admin)
            ->post('/admin/schedules', [
                'class_id' => $this->class2->id,
                'subject_id' => $this->subject2->id,
                'teacher_id' => $this->teacher->id,
                'day' => 'Monday',
                'week_type' => 'semua',
                'start_time' => '09:30',
                'end_time' => '11:00',
                'room_id' => $this->room1->id,
            ]);

        $response->assertRedirect('/admin/schedules');
        $response->assertSessionHas('success', 'Jadwal berhasil ditambahkan!');
        
        // Assert both schedules exist
        $this->assertEquals(2, Schedule::count());
    }
}
