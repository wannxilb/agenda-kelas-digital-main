<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleModeToggleTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = User::where('email', 'admin@school.com')->first();
        Schedule::truncate();
    }

    public function test_admin_can_view_schedule_mode_toggle(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.schedules.index'));

        $response->assertOk();
        $response->assertSee('Mode');
        $response->assertSee('Block');
        $response->assertSee('Normal');
        $response->assertSee('setScheduleMode', false);
    }

    public function test_toggle_switches_to_normal_mode(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.schedules.mode'), ['mode' => 'normal']);

        $response->assertOk();
        $response->assertJson(['success' => true, 'mode' => 'normal']);
        $this->assertSame('normal', Setting::get('schedule_mode', 'block', $this->admin->institution_id));
    }

    public function test_toggle_switches_to_block_mode(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.schedules.mode'), ['mode' => 'block']);

        $response->assertOk();
        $response->assertJson(['success' => true, 'mode' => 'block']);
        $this->assertSame('block', Setting::get('schedule_mode', 'block', $this->admin->institution_id));
    }

    public function test_toggle_rejects_invalid_mode(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.schedules.mode'), ['mode' => 'invalid']);

        $response->assertStatus(422);
        $this->assertSame('block', Setting::scheduleMode());
    }
}
