<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Schedule;
use Illuminate\Support\Carbon;

class RoomAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected $class1;
    protected $subject1;
    protected $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->class1 = Classes::first();
        $this->subject1 = Subject::first();
        $this->teacher = User::where('email', 'guru@school.com')->first();
    }

    public function test_room_is_occupied_when_in_schedule(): void
    {
        // Set time to Monday at 09:00
        $now = Carbon::create(2026, 5, 25, 9, 0, 0); // Monday

        Schedule::create([
            'class_id' => $this->class1->id,
            'subject_id' => $this->subject1->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'room' => 'Lab 101',
        ]);

        $isOccupied = Schedule::currentlyOccupied($now)
            ->where('room', 'Lab 101')
            ->exists();

        $this->assertTrue($isOccupied);
    }

    public function test_room_is_available_when_not_in_schedule(): void
    {
        // Set time to Monday at 11:00
        $now = Carbon::create(2026, 5, 25, 11, 0, 0); // Monday

        Schedule::create([
            'class_id' => $this->class1->id,
            'subject_id' => $this->subject1->id,
            'teacher_id' => $this->teacher->id,
            'day' => 'Monday',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'room' => 'Lab 101',
        ]);

        $isOccupied = Schedule::currentlyOccupied($now)
            ->where('room', 'Lab 101')
            ->exists();

        $this->assertFalse($isOccupied);
    }
}
