<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class AttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'siswa']);
    }

    public function test_attendance_report_excludes_graduated_students()
    {
        // Create a class
        $class = \App\Models\Classes::create([
            'name' => 'XII IPA 1',
            'grade_level' => 'XII',
            'academic_year' => '2026/2027',
        ]);

        // 1. Setup: Create a student and a graduated student
        $student = User::factory()->create(['status' => 'active', 'class_id' => $class->id]);
        $student->assignRole('siswa');
        
        $graduatedStudent = User::factory()->create(['status' => 'graduated', 'class_id' => $class->id]);
        $graduatedStudent->assignRole('siswa');
        
        // 2. Setup: Admin user
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $this->actingAs($admin);
        
        // 3. Act: Request the report
        $response = $this->get(route('admin.reports.attendance'));
        
        // 4. Assert: Check the view data
        $response->assertStatus(200);
        
        // The view data should have $students
        $students = $response->viewData('students');
        
        $this->assertTrue($students->contains($student));
        $this->assertFalse($students->contains($graduatedStudent));
    }
}
