<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Classes;
use App\Models\AcademicYear;
use App\Models\ClassHistory;

class AcademicYearAndPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $student;
    protected $class1;
    protected $class2;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles, permissions, classes, users
        $this->seed();

        $this->admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@school.test',
        ]);
        $this->admin->assignRole('admin');

        $this->class1 = Classes::create([
            'name' => 'XI PREVIEW 1',
            'grade_level' => 'XI',
            'major' => 'PREVIEW',
            'academic_year' => '2024/2025',
            'capacity' => 36,
            'is_active' => true,
        ]);

        $this->class2 = Classes::create([
            'name' => 'XII PREVIEW 1',
            'grade_level' => 'XII',
            'major' => 'PREVIEW',
            'academic_year' => '2025/2026',
            'capacity' => 36,
            'is_active' => true,
        ]);

        $this->student = User::factory()->create([
            'name' => 'Siswa Test',
            'email' => 'siswa@school.test',
            'class_id' => $this->class1->id,
            'status' => 'active',
        ]);
        $this->student->assignRole('siswa');
    }

    /**
     * Test academic years management.
     */
    public function test_admin_can_manage_academic_years(): void
    {
        // 1. View Index
        $response = $this->actingAs($this->admin)
            ->get('/admin/academic-years');
        $response->assertStatus(200);

        // 2. Store Academic Year
        $response = $this->actingAs($this->admin)
            ->post('/admin/academic-years', [
                'name' => '2030/2031',
                'semester' => 'Ganjil',
                'start_date' => '2025-07-01',
                'end_date' => '2026-06-30',
            ]);
        $response->assertRedirect('/admin/academic-years');
        $response->assertSessionHas('success', 'Tahun Ajaran berhasil ditambahkan.');

        $this->assertDatabaseHas('academic_years', [
            'name' => '2030/2031',
            'semester' => 'Ganjil',
            'is_active' => false,
        ]);

        $newYear = AcademicYear::where('name', '2030/2031')->where('semester', 'Ganjil')->first();

        // 3. Set Active Academic Year
        $response = $this->actingAs($this->admin)
            ->post("/admin/academic-years/{$newYear->id}/set-active");
        $response->assertRedirect('/admin/academic-years');
        $response->assertSessionHas('success', 'Tahun Ajaran 2030/2031 (Ganjil) berhasil diaktifkan.');

        $this->assertTrue($newYear->fresh()->is_active);

        // 4. Update Academic Year
        $response = $this->actingAs($this->admin)
            ->put("/admin/academic-years/{$newYear->id}", [
                'name' => '2030/2031 (Updated)',
                'semester' => 'Genap',
                'start_date' => '2025-07-01',
                'end_date' => '2026-06-30',
            ]);
        $response->assertRedirect('/admin/academic-years');
        $response->assertSessionHas('success', 'Tahun Ajaran berhasil diperbarui.');

        $this->assertDatabaseHas('academic_years', [
            'id' => $newYear->id,
            'name' => '2030/2031 (Updated)',
            'semester' => 'Genap',
        ]);
    }

    /**
     * Test student promotion index and preview.
     */
    public function test_admin_can_view_promotion_index_and_preview(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/class-promotions');
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)
            ->getJson("/admin/class-promotions/preview?class_id={$this->class1->id}");
        $response->assertStatus(200)
            ->assertJsonStructure([
                'students' => [
                    '*' => ['id', 'name', 'nis']
                ]
            ]);
    }

    /**
     * Test student promotion process.
     */
    public function test_admin_can_promote_students(): void
    {
        $oldHomeroom = User::factory()->create(['name' => 'Wali Lama']);
        $oldHomeroom->assignRole('teacher');
        $oldHomeroom->assignRole('wali_kelas');

        $newHomeroom = User::factory()->create(['name' => 'Wali Baru']);
        $newHomeroom->assignRole('teacher');

        $sourceClass = Classes::create([
            'name' => 'X TEST 1',
            'grade_level' => 'X',
            'major' => 'TEST',
            'academic_year' => '2024/2025',
            'homeroom_teacher_id' => $oldHomeroom->id,
            'capacity' => 36,
            'is_active' => true,
        ]);

        $targetClass = Classes::create([
            'name' => 'XI TEST 1',
            'grade_level' => 'XI',
            'major' => 'TEST',
            'academic_year' => '2031/2032',
            'homeroom_teacher_id' => null,
            'capacity' => 36,
            'is_active' => true,
        ]);

        $this->student->update(['class_id' => $sourceClass->id, 'status' => 'active']);

        // 1. Create a target academic year
        $targetYear = AcademicYear::create([
            'name' => '2031/2032',
            'semester' => 'Ganjil',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => false,
        ]);

        // Deactivate all existing academic years
        AcademicYear::query()->update(['is_active' => false]);

        // 2. Set current active year (source year)
        $sourceYear = AcademicYear::create([
            'name' => '2024/2025',
            'semester' => 'Genap',
            'start_date' => '2024-07-01',
            'end_date' => '2025-06-30',
            'is_active' => true,
        ]);

        // 3. Perform promotion
        $response = $this->actingAs($this->admin)
            ->from('/admin/class-promotions')
            ->post('/admin/class-promotions/promote', [
                'target_year_id' => $targetYear->id,
                'promotions' => [[
                    'source_class_id' => $sourceClass->id,
                    'target_class_id' => $targetClass->id,
                    'new_homeroom_id' => $newHomeroom->id,
                    'student_ids' => [$this->student->id],
                ]],
            ]);

        $response->assertRedirect('/admin/class-promotions');
        $response->assertSessionHas('success');

        // 4. Verify user class is updated
        $this->assertEquals($targetClass->id, $this->student->fresh()->class_id);
        $this->assertNull($sourceClass->fresh()->homeroom_teacher_id);
        $this->assertEquals($newHomeroom->id, $targetClass->fresh()->homeroom_teacher_id);
        $this->assertTrue($newHomeroom->fresh()->hasRole('wali_kelas'));
        $this->assertTrue($oldHomeroom->fresh()->hasRole('wali_kelas'));

        // 5. Verify class history contains records for both years
        $this->assertDatabaseHas('class_histories', [
            'user_id' => $this->student->id,
            'academic_year_id' => $sourceYear->id,
            'class_id' => $sourceClass->id,
            'homeroom_teacher_id' => $oldHomeroom->id,
        ]);

        $this->assertDatabaseHas('class_histories', [
            'user_id' => $this->student->id,
            'academic_year_id' => $targetYear->id,
            'class_id' => $targetClass->id,
            'homeroom_teacher_id' => $newHomeroom->id,
        ]);
    }

    /**
     * Test that partial promotion does not move non-promoted students,
     * including a sekretaris who did not naik kelas, and keeps their history.
     */
    public function test_partial_promotion_keeps_non_promoted_students_in_source_class(): void
    {
        $sourceClass = Classes::create([
            'name' => 'X MIX 1',
            'grade_level' => 'X',
            'major' => 'MIX',
            'academic_year' => '2024/2025',
            'homeroom_teacher_id' => null,
            'capacity' => 36,
            'is_active' => true,
        ]);

        $targetClass = Classes::create([
            'name' => 'XI MIX 1',
            'grade_level' => 'XI',
            'major' => 'MIX',
            'academic_year' => '2031/2032',
            'homeroom_teacher_id' => null,
            'capacity' => 36,
            'is_active' => true,
        ]);

        $promoted = $this->student;
        $promoted->update(['class_id' => $sourceClass->id, 'status' => 'active']);

        $staying = User::factory()->create([
            'name' => 'Siswa Tetap',
            'email' => 'tetap@school.test',
            'class_id' => $sourceClass->id,
            'status' => 'active',
        ]);
        $staying->assignRole('siswa');

        $secretary = User::factory()->create([
            'name' => 'Sekretaris Tetap',
            'email' => 'sekretaris@school.test',
            'class_id' => $sourceClass->id,
            'status' => 'active',
        ]);
        $secretary->assignRole('siswa');
        $secretary->assignRole('sekretaris');

        $targetYear = AcademicYear::create([
            'name' => '2031/2032',
            'semester' => 'Ganjil',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => false,
        ]);

        AcademicYear::query()->update(['is_active' => false]);

        $sourceYear = AcademicYear::create([
            'name' => '2024/2025',
            'semester' => 'Genap',
            'start_date' => '2024-07-01',
            'end_date' => '2025-06-30',
            'is_active' => true,
        ]);

        // Only the promoted student is selected; staying + secretary are NOT promoted.
        $response = $this->actingAs($this->admin)
            ->from('/admin/class-promotions')
            ->post('/admin/class-promotions/promote', [
                'target_year_id' => $targetYear->id,
                'promotions' => [[
                    'source_class_id' => $sourceClass->id,
                    'target_class_id' => $targetClass->id,
                    'student_ids' => [$promoted->id],
                ]],
            ]);

        $response->assertRedirect('/admin/class-promotions');
        $response->assertSessionHas('success');

        // 1. Promoted student moved to the target class.
        $this->assertEquals($targetClass->id, $promoted->fresh()->class_id);

        // 2. Non-promoted students (incl. sekretaris) stay in the source class.
        $this->assertEquals($sourceClass->id, $staying->fresh()->class_id);
        $this->assertEquals($sourceClass->id, $secretary->fresh()->class_id);

        // 3. History snapshots recorded for non-promoted students.
        foreach ([$staying, $secretary] as $user) {
            foreach ([$sourceYear, $targetYear] as $year) {
                $this->assertDatabaseHas('class_histories', [
                    'user_id'          => $user->id,
                    'academic_year_id' => $year->id,
                    'class_id'         => $sourceClass->id,
                ]);
            }
        }
    }

    /**
     * Test that editing a student's profile does not overwrite the class
     * history recorded for the active (source) academic year by promotion.
     */
    public function test_editing_student_does_not_overwrite_promotion_history(): void
    {
        $sourceClass = Classes::create([
            'name' => 'XI GUARD 1',
            'grade_level' => 'XI',
            'major' => 'GUARD',
            'academic_year' => '2025/2026',
            'capacity' => 36,
            'is_active' => true,
        ]);

        $targetClass = Classes::create([
            'name' => 'XII GUARD 1',
            'grade_level' => 'XII',
            'major' => 'GUARD',
            'academic_year' => '2026/2027',
            'capacity' => 36,
            'is_active' => true,
        ]);

        $student = User::factory()->create([
            'name' => 'Siswa Sekretaris Guard',
            'email' => 'sekretarisguard@school.test',
            'nis' => 'SIS-GUARD-001',
            'gender' => 'L',
            'class_id' => $targetClass->id,
            'status' => 'active',
        ]);
        $student->assignRole('siswa');

        AcademicYear::query()->update(['is_active' => false]);

        $sourceYear = AcademicYear::create([
            'name' => '2025/2026',
            'semester' => 'Genap',
            'is_active' => true,
        ]);

        $targetYear = AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        // History as produced by promotion: source year = XI, target year = XII.
        ClassHistory::create([
            'user_id'          => $student->id,
            'academic_year_id' => $sourceYear->id,
            'class_id'         => $sourceClass->id,
        ]);
        ClassHistory::create([
            'user_id'          => $student->id,
            'academic_year_id' => $targetYear->id,
            'class_id'         => $targetClass->id,
        ]);

        // Admin re-saves the profile keeping the current (target) class selected.
        $response = $this->actingAs($this->admin)
            ->put('/admin/students/' . $student->id, [
                'name'      => 'Siswa Sekretaris Guard',
                'nis'       => 'SIS-GUARD-001',
                'gender'    => 'L',
                'class_id'  => $targetClass->id,
                'status'    => 'active',
            ]);

        $response->assertSessionHas('success');

        // The source-year history must remain the source (XI) class.
        $this->assertDatabaseHas('class_histories', [
            'user_id'          => $student->id,
            'academic_year_id' => $sourceYear->id,
            'class_id'         => $sourceClass->id,
        ]);

        // And it must NOT have been overwritten with the target (XII) class.
        $this->assertDatabaseMissing('class_histories', [
            'user_id'          => $student->id,
            'academic_year_id' => $sourceYear->id,
            'class_id'         => $targetClass->id,
        ]);
    }

    /**
     * End-to-end: the student history and academic-records pages render the
     * correct class (XI) for the active year after promotion, not XII.
     */
    public function test_student_history_and_academic_records_reflect_source_year_class(): void
    {
        $sourceClass = Classes::create([
            'name' => 'IXI HISTORY 1',
            'grade_level' => 'XI',
            'major' => 'HISTORY',
            'academic_year' => '2025/2026',
            'capacity' => 36,
            'is_active' => true,
        ]);

        $targetClass = Classes::create([
            'name' => 'IXII HISTORY 1',
            'grade_level' => 'XII',
            'major' => 'HISTORY',
            'academic_year' => '2026/2027',
            'capacity' => 36,
            'is_active' => true,
        ]);

        $student = User::factory()->create([
            'name' => 'Siswa Riwayat QA',
            'email' => 'riwayatqa@school.test',
            'nis' => 'SIS-HIST-001',
            'gender' => 'P',
            'class_id' => $targetClass->id,
            'status' => 'active',
        ]);
        $student->assignRole('siswa');

        AcademicYear::query()->update(['is_active' => false]);

        $sourceYear = AcademicYear::create([
            'name' => '2025/2026',
            'semester' => 'Genap',
            'is_active' => true,
        ]);

        AcademicYear::create([
            'name' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        ClassHistory::create([
            'user_id'          => $student->id,
            'academic_year_id' => $sourceYear->id,
            'class_id'         => $sourceClass->id,
        ]);

        $targetYearId = AcademicYear::where('name', '2026/2027')->value('id');
        ClassHistory::create([
            'user_id'          => $student->id,
            'academic_year_id' => $targetYearId,
            'class_id'         => $targetClass->id,
        ]);

        // 1. Student history page (per-student riwayat akademik).
        $response = $this->actingAs($this->admin)->get('/admin/students/' . $student->id . '/history');
        $response->assertStatus(200);
        $response->assertSee($sourceClass->name);

        // 2. Academic records index page lists classes with history.
        $response = $this->actingAs($this->admin)->get('/admin/academic-records');
        $response->assertStatus(200);

        // 3. Academic records detail page for the active-year class.
        $yearName = urlencode($sourceYear->name);
        $response = $this->actingAs($this->admin)
            ->get("/admin/academic-records/{$sourceClass->id}/show/{$yearName}");
        $response->assertStatus(200);
        $response->assertSee('Siswa Riwayat QA');
    }
}
