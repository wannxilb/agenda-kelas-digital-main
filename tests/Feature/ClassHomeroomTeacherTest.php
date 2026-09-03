<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Classes;
use Spatie\Permission\Models\Role;

class ClassHomeroomTeacherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'teacher']);
        Role::create(['name' => 'wali_kelas']);
        Role::create(['name' => 'admin']);
    }

    public function test_a_teacher_cannot_be_homeroom_teacher_for_two_active_classes(): void
    {
        $this->withoutMiddleware();
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        // Create first class
        Classes::create([
            'name' => 'Class 1',
            'major' => 'RPL',
            'grade_level' => 'X',
            'academic_year' => '2025/2026',
            'homeroom_teacher_id' => $teacher->id,
            'capacity' => 30,
            'is_active' => true,
        ]);

        // Try to create second class with same teacher
        $response = $this->post(route('admin.classes.store'), [
            'name' => 'Class 2',
            'major' => 'RPL',
            'grade_level' => 'XI',
            'academic_year' => '2025/2026',
            'homeroom_teacher_id' => $teacher->id,
            'capacity' => 30,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('homeroom_teacher_id');
        $this->assertEquals(1, Classes::where('homeroom_teacher_id', $teacher->id)->count());
    }

    public function test_wali_kelas_role_is_removed_when_teacher_is_removed_from_class(): void
    {
        $this->withoutMiddleware();
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        // Create class with teacher
        $class = Classes::create([
            'name' => 'Class 1',
            'major' => 'RPL',
            'grade_level' => 'X',
            'academic_year' => '2025/2026',
            'homeroom_teacher_id' => $teacher->id,
            'capacity' => 30,
            'is_active' => true,
        ]);
        $teacher->assignRole('wali_kelas');

        // Update class to remove teacher
        $response = $this->put(route('admin.classes.update', $class->id), [
            'name' => 'Class 1',
            'major' => 'RPL',
            'grade_level' => 'X',
            'academic_year' => '2025/2026',
            'homeroom_teacher_id' => null,
            'capacity' => 30,
            'is_active' => true,
        ]);
        $response->assertStatus(302);

        $class->refresh();
        $this->assertTrue(empty($class->homeroom_teacher_id));

        $teacher->refresh();
        $this->assertFalse($teacher->hasRole('wali_kelas'));
    }

    public function test_wali_kelas_role_is_removed_when_class_is_deleted(): void
    {
        $this->withoutMiddleware();
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        // Create class with teacher
        $class = Classes::create([
            'name' => 'Class 1',
            'major' => 'RPL',
            'grade_level' => 'X',
            'academic_year' => '2025/2026',
            'homeroom_teacher_id' => $teacher->id,
            'capacity' => 30,
            'is_active' => true,
        ]);
        $teacher->assignRole('wali_kelas');

        // Delete class
        $this->delete(route('admin.classes.destroy', $class->id));

        $teacher->refresh();
        $this->assertFalse($teacher->hasRole('wali_kelas'));
    }

    public function test_admin_can_filter_classes_by_grade_level_major_and_teacher(): void
    {
        $this->withoutMiddleware();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $teacher1 = User::factory()->create();
        $teacher1->assignRole('teacher');
        $teacher2 = User::factory()->create();
        $teacher2->assignRole('teacher');

        // Create class 1: Grade X, Major RPL, Teacher 1
        $class1 = Classes::create([
            'name' => 'Class X-RPL-1',
            'major' => 'RPL',
            'grade_level' => 'X',
            'academic_year' => '2025/2026',
            'homeroom_teacher_id' => $teacher1->id,
            'capacity' => 30,
            'is_active' => true,
        ]);

        // Create class 2: Grade XI, Major TKJ, Teacher 2
        $class2 = Classes::create([
            'name' => 'Class XI-TKJ-1',
            'major' => 'TKJ',
            'grade_level' => 'XI',
            'academic_year' => '2025/2026',
            'homeroom_teacher_id' => $teacher2->id,
            'capacity' => 30,
            'is_active' => true,
        ]);

        // 1. Test Filter by Grade Level X
        $response = $this->get(route('admin.classes.index', ['grade_level' => 'X']));
        $response->assertStatus(200);
        $response->assertSee('Class X-RPL-1');
        $response->assertDontSee('Class XI-TKJ-1');

        // 2. Test Filter by Major TKJ
        $response = $this->get(route('admin.classes.index', ['major' => 'TKJ']));
        $response->assertStatus(200);
        $response->assertDontSee('Class X-RPL-1');
        $response->assertSee('Class XI-TKJ-1');

        // 3. Test Filter by Teacher 1
        $response = $this->get(route('admin.classes.index', ['teacher_id' => $teacher1->id]));
        $response->assertStatus(200);
        $response->assertSee('Class X-RPL-1');
        $response->assertDontSee('Class XI-TKJ-1');
    }
}

