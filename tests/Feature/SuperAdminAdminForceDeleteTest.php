<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Agenda;
use Illuminate\Support\Facades\DB;

class SuperAdminAdminForceDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable FK enforcement so cascade deletes behave like production.
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }

        $this->seed();

        $this->superAdmin = User::where('email', 'superadmin@school.com')->firstOrFail();
    }

    private function makeSchoolAdmin(): User
    {
        $admin = User::factory()->create([
            'name' => 'Admin Sekolah Tes',
            'email' => 'admin-tes@school.test',
            'institution_id' => 1,
            'status' => 'active',
        ]);
        $admin->assignRole('admin');

        return $admin;
    }

    private function makeAgendaFor(User $user): Agenda
    {
        $class = Classes::create([
            'name' => 'X TES',
            'grade_level' => 'X',
            'major' => 'TES',
            'academic_year' => '2025/2026',
            'capacity' => 36,
            'is_active' => true,
        ]);

        $subject = Subject::create([
            'name' => 'Mapel Tes',
            'credit_hours' => 2,
            'institution_id' => 1,
        ]);

        $activeYear = AcademicYear::where('is_active', true)->first();

        return Agenda::create([
            'class_id' => $class->id,
            'teacher_id' => $user->id,
            'subject_id' => $subject->id,
            'date' => now()->toDateString(),
            'title' => 'Agenda Tes',
            'description' => 'Deskripsi tes',
            'status' => 'published',
            'academic_year_id' => $activeYear?->id,
            'institution_id' => 1,
        ]);
    }

    public function test_force_delete_without_linked_data_deletes_immediately(): void
    {
        $admin = $this->makeSchoolAdmin();
        $admin->delete();

        $this->actingAs($this->superAdmin)
            ->delete(route('super-admin.admins.forceDelete', $admin->id))
            ->assertRedirect(route('super-admin.admins.trash'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }

    public function test_force_delete_with_agenda_asks_confirmation_before_deleting(): void
    {
        $admin = $this->makeSchoolAdmin();
        $agenda = $this->makeAgendaFor($admin);
        $admin->delete();

        // First hit: show confirmation page, admin must NOT be deleted yet.
        $this->actingAs($this->superAdmin)
            ->delete(route('super-admin.admins.forceDelete', $admin->id))
            ->assertStatus(200)
            ->assertViewIs('super_admin.admins.force-delete-confirm');

        $this->assertNotNull(User::onlyTrashed()->find($admin->id));

        // Second hit with continue=1: proceed and cascade-delete linked agenda.
        $this->actingAs($this->superAdmin)
            ->delete(route('super-admin.admins.forceDelete', [$admin->id, 'continue' => 1]))
            ->assertRedirect(route('super-admin.admins.trash'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
        $this->assertDatabaseMissing('agendas', ['id' => $agenda->id]);
    }
}