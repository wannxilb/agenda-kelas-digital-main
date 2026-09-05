<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Agenda;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\ClassHistory;
use App\Models\GradeAssignment;
use App\Models\Major;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\StudentGrade;
use App\Models\Subject;
use App\Models\TeacherStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AllFeaturesSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $superAdmin;
    protected User $guru;
    protected User $wali;
    protected User $wakasek;
    protected User $sekretaris;
    protected User $siswaUser;

    protected AcademicYear $activeYear;
    protected Classes $classA;
    protected Classes $classB;
    protected Subject $subject;
    protected Room $room;
    protected ?Schedule $scheduleMonday;
    protected Agenda $agenda;
    protected GradeAssignment $assignment;
    protected StudentGrade $studentGrade;
    protected TeacherStatus $teacherStatus;
    protected Major $major;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        // Aktifkan semua fitur & buat jam operasional terbuka agar seluruh
        // halaman (termasuk create/edit di belakang middleware operational.hours)
        // bisa dirender deterministik.
        Setting::set('feature_export_excel', '1', 'general', 1);
        Setting::set('operational_override_until', '2099-12-31 23:59:59', 'general', 1);
        Setting::set('school_location_enabled', '0', 'general', 1);
        Setting::set('schedule_mode', 'normal', 'general', 1);
        Cache::flush();

        $this->activeYear = AcademicYear::where('is_active', true)->firstOrFail();
        $this->admin = User::where('email', 'admin@school.com')->firstOrFail();
        $this->superAdmin = User::where('email', 'superadmin@school.com')->firstOrFail();

        $this->guru = $this->makeUser('Guru QA', 'teacher');
        $this->wali = $this->makeUser('Wali Kelas QA', 'wali_kelas');
        $this->wakasek = $this->makeUser('Wakasek QA', 'wakasek');
        $this->sekretaris = $this->makeUser('Sekretaris QA', 'sekretaris');
        $this->siswaUser = $this->makeUser('Siswa QA', 'siswa');

        $this->classA = Classes::create([
            'name' => 'XI QA 1',
            'grade_level' => 'XI',
            'major' => 'QA',
            'academic_year' => '2025/2026',
            'homeroom_teacher_id' => $this->wali->id,
            'capacity' => 36,
            'is_active' => true,
            'institution_id' => 1,
        ]);

        $this->classB = Classes::create([
            'name' => 'XII QA 1',
            'grade_level' => 'XII',
            'major' => 'QA',
            'academic_year' => '2025/2026',
            'homeroom_teacher_id' => $this->guru->id,
            'capacity' => 36,
            'is_active' => true,
            'institution_id' => 1,
        ]);

        // Konteks kelas untuk siswa & sekretaris
        $this->sekretaris->update(['class_id' => $this->classA->id]);
        $this->siswaUser->update(['class_id' => $this->classA->id]);

        foreach ([$this->siswaUser, $this->sekretaris, $this->wali] as $ctxUser) {
            ClassHistory::create([
                'user_id' => $ctxUser->id,
                'class_id' => $this->classA->id,
                'academic_year_id' => $this->activeYear->id,
                'homeroom_teacher_id' => $ctxUser->id === $this->wali->id ? $this->wali->id : null,
                'institution_id' => 1,
            ]);
        }

        $this->subject = Subject::create([
            'name' => 'Pemrograman QA',
            'description' => 'Mapel untuk smoke test',
            'credit_hours' => 4,
            'institution_id' => 1,
        ]);
        $this->subject->teachers()->attach($this->guru->id);

        $this->room = Room::create([
            'name' => 'Ruang QA 1',
            'type' => 'Kelas',
            'capacity' => 30,
            'is_active' => true,
            'institution_id' => 1,
        ]);

        // Jadwal guru: Senin–Jumat di kelas A + tambahan di kelas B
        $this->scheduleMonday = null;
        foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $i => $day) {
            $schedule = Schedule::create([
                'class_id' => $this->classA->id,
                'subject_id' => $this->subject->id,
                'teacher_id' => $this->guru->id,
                'day' => $day,
                'week_type' => 'semua',
                'start_time' => '07:00:00',
                'end_time' => '08:00:00',
                'room' => $this->room->name,
                'room_id' => $this->room->id,
                'academic_year_id' => $this->activeYear->id,
                'institution_id' => 1,
            ]);
            if ($i === 0) {
                $this->scheduleMonday = $schedule;
            }
        }
        Schedule::create([
            'class_id' => $this->classB->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->guru->id,
            'day' => 'Monday',
            'week_type' => 'semua',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'room' => $this->room->name,
            'room_id' => $this->room->id,
            'academic_year_id' => $this->activeYear->id,
            'institution_id' => 1,
        ]);

        $this->agenda = Agenda::create([
            'class_id' => $this->classA->id,
            'teacher_id' => $this->guru->id,
            'subject_id' => $this->subject->id,
            'schedule_id' => $this->scheduleMonday->id,
            'room' => $this->room->name,
            'date' => now()->format('Y-m-d'),
            'title' => 'Agenda Harian QA',
            'description' => 'Deskripsi agenda harian untuk smoke test.',
            'status' => 'published',
            'academic_year_id' => $this->activeYear->id,
            'institution_id' => 1,
        ]);

        foreach (['present', 'late', 'excused', 'sick', 'absent'] as $idx => $status) {
            Attendance::create([
                'student_id' => $this->siswaUser->id,
                'class_id' => $this->classA->id,
                'date' => now()->subDays($idx + 1)->format('Y-m-d'),
                'status' => $status,
                'academic_year_id' => $this->activeYear->id,
                'institution_id' => 1,
            ]);
        }

        $this->assignment = GradeAssignment::create([
            'teacher_id' => $this->guru->id,
            'class_id' => $this->classA->id,
            'subject_id' => $this->subject->id,
            'title' => 'Tugas QA',
            'description' => 'Deskripsi tugas QA',
            'assigned_date' => now()->format('Y-m-d'),
            'due_date' => now()->addWeek()->format('Y-m-d'),
            'max_score' => 100,
            'academic_year_id' => $this->activeYear->id,
            'institution_id' => 1,
        ]);

        $this->studentGrade = StudentGrade::create([
            'grade_assignment_id' => $this->assignment->id,
            'student_id' => $this->siswaUser->id,
            'score' => 90,
            'institution_id' => 1,
        ]);

        // Izin/tugas luar: 1 pending (untuk guru & wakasek) + 1 approved
        $this->teacherStatus = TeacherStatus::create([
            'teacher_id' => $this->guru->id,
            'type' => 'sakit',
            'status' => 'pending',
            'date' => now()->format('Y-m-d'),
            'start_time' => '07:00:00',
            'end_time' => '16:00:00',
            'note' => 'Sakit untuk smoke test',
            'academic_year_id' => $this->activeYear->id,
            'institution_id' => 1,
        ]);
        TeacherStatus::create([
            'teacher_id' => $this->guru->id,
            'type' => 'izin',
            'status' => 'approved',
            'date' => now()->subWeek()->format('Y-m-d'),
            'start_time' => '07:00:00',
            'end_time' => '16:00:00',
            'approver_id' => $this->wakasek->id,
            'processed_at' => now(),
            'academic_year_id' => $this->activeYear->id,
            'institution_id' => 1,
        ]);

        $this->major = Major::firstOrFail();
    }

    private function makeUser(string $name, string $role): User
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)) . '@school.test',
            'institution_id' => 1,
            'status' => 'active',
        ]);
        $user->syncRoles($role);

        return $user;
    }

    private function assertPage(User $user, string $uri, int $expected = 200): void
    {
        $response = $this->actingAs($user)->get($uri);
        $code = $response->getStatusCode();
        $this->assertTrue(
            $code === $expected,
            "Halaman {$uri} untuk {$user->email} mengembalikan status {$code}, diharapkan {$expected}."
        );
    }

    /**
     * Varian fleksibel: halaman boleh 200/302/303/307 (mis. redirect atau
     * unduhan PDF/Excel), tapi bukan 403/404/500.
     */
    private function assertPageOk(User $user, string $uri): void
    {
        $response = $this->actingAs($user)->get($uri);
        $code = $response->getStatusCode();
        $this->assertTrue(
            in_array($code, [200, 302, 303, 307], true),
            "Halaman {$uri} untuk {$user->email} mengembalikan status {$code}."
        );
    }

    private function assertPages(User $user, array $uris, int $expected = 200): void
    {
        foreach ($uris as $uri) {
            $this->assertPage($user, $uri, $expected);
        }
    }

    private function assertPagesOk(User $user, array $uris): void
    {
        foreach ($uris as $uri) {
            $this->assertPageOk($user, $uri);
        }
    }

    // ------------------------------------------------------------------
    // Auth
    // ------------------------------------------------------------------

    public function test_guest_hits_login_and_root_redirect(): void
    {
        $this->get('/')->assertStatus(302);
        $this->get('/login')->assertOk();
        $this->post('/login', [
            'email' => $this->guru->email,
            'password' => 'password',
        ])->assertRedirect();
    }

    // ------------------------------------------------------------------
    // Admin
    // ------------------------------------------------------------------

    public function test_admin_can_access_all_admin_pages(): void
    {
        $this->assertPages($this->admin, [
            '/admin/dashboard',
            '/admin/profile',
            '/admin/search?q=siswa',
            '/admin/settings',
            '/admin/activities',
            '/admin/academic-years',
            '/admin/class-promotions',
            '/admin/academic-records',
            '/admin/daily-attendance/corrections',
            '/admin/daily-attendance/settings/edit',
            '/admin/monitoring/classes',
            '/admin/monitoring/rooms',
            '/admin/monitoring/teachers',
            '/admin/teacher-status/report',
            '/admin/reports/attendance',
            '/admin/students',
            '/admin/students/create',
            '/admin/students/bulk-graduation',
            '/admin/teachers',
            '/admin/teachers/create',
            '/admin/subjects',
            '/admin/subjects/create',
            '/admin/classes',
            '/admin/classes/create',
            '/admin/rooms',
            '/admin/rooms/create',
            '/admin/majors',
            '/admin/majors/create',
            '/admin/schedules',
            '/admin/schedules/create',
        ]);

        $id = $this->siswaUser->id;
        $this->assertPages($this->admin, [
            "/admin/students/{$id}",
            "/admin/students/{$id}/edit",
            "/admin/students/{$id}/history",
            "/admin/teachers/{$this->guru->id}",
            "/admin/teachers/{$this->guru->id}/edit",
            "/admin/subjects/{$this->subject->id}",
            "/admin/subjects/{$this->subject->id}/edit",
            "/admin/classes/{$this->classA->id}",
            "/admin/classes/{$this->classA->id}/edit",
            "/admin/rooms/{$this->room->id}/edit",
            "/admin/majors/{$this->major->id}/edit",
            "/admin/schedules/{$this->scheduleMonday->id}",
            "/admin/schedules/{$this->scheduleMonday->id}/edit",
            "/admin/reports/attendance/student/{$id}",
            "/admin/academic-records/{$this->classA->id}/show/" . rawurlencode($this->activeYear->name),
        ]);

        $this->assertPagesOk($this->admin, [
            '/admin/dashboard/api/stats',
            '/admin/class-promotions/preview',
            '/admin/schedules/get-available-rooms',
            '/admin/monitoring/classes/api',
            '/admin/monitoring/teachers/api',
            '/admin/reports/export/pdf',
            '/admin/reports/export/excel',
            '/admin/teacher-status/report/csv',
            '/admin/teacher-status/report/pdf',
            '/admin/students/export/template',
            '/admin/students/export/data',
            '/admin/teachers/export/template',
            '/admin/subjects/export/template',
            '/admin/schedules/export/template',
        ]);

        // Pengaturan harian dialihkan ke halaman settings.
        $this->assertPage($this->admin, '/admin/daily-attendance/settings', 302);

        // Halaman yang tidak seharusnya diakses role lain harus terblokir.
        $this->get('/guru/dashboard')->assertForbidden();
        $this->get('/wali-kelas/dashboard')->assertForbidden();
        $this->get('/siswa/dashboard')->assertForbidden();
        $this->get('/wakasek/dashboard')->assertForbidden();
        $this->get('/super-admin/dashboard')->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Guru
    // ------------------------------------------------------------------

    public function test_guru_can_access_all_guru_pages(): void
    {
        $this->assertPages($this->guru, [
            '/guru/dashboard',
            '/guru/profile',
            '/guru/attendance',
            '/guru/journal',
            '/guru/agenda',
            '/guru/agenda/archive',
            '/guru/agenda/create',
            '/guru/teacher-status',
            '/guru/teacher-status/create',
            '/guru/grades',
            '/guru/grades/create',
            '/guru/report',
        ]);

        $this->assertPages($this->guru, [
            "/guru/agenda/{$this->agenda->id}",
            "/guru/agenda/{$this->agenda->id}/edit",
            "/guru/agenda/{$this->agenda->id}/preview",
            "/guru/teacher-status/{$this->teacherStatus->id}",
            "/guru/teacher-status/{$this->teacherStatus->id}/edit",
            "/guru/grades/{$this->assignment->id}",
            "/guru/grades/{$this->assignment->id}/edit",
            "/guru/grades/summary/{$this->classA->id}/{$this->subject->id}",
        ]);

        $this->assertPagesOk($this->guru, [
            '/guru/agenda/get-schedule-info?class_id=' . $this->classA->id . '&date=' . now()->format('Y-m-d'),
            "/guru/grades/{$this->assignment->id}/export",
            "/guru/grades/summary/{$this->classA->id}/{$this->subject->id}/export",
            '/guru/report/export',
            '/guru/report/agenda/export/excel',
            '/guru/report/agenda/export/pdf',
        ]);
    }

    // ------------------------------------------------------------------
    // Wali Kelas
    // ------------------------------------------------------------------

    public function test_wali_kelas_can_access_all_wali_kelas_pages(): void
    {
        $this->assertPages($this->wali, [
            '/wali-kelas/dashboard',
            '/wali-kelas/profile',
            '/wali-kelas/agenda',
            '/wali-kelas/agenda/archive',
            '/wali-kelas/attendance',
            '/wali-kelas/attendance/report',
            '/wali-kelas/daily-attendance',
            '/wali-kelas/daily-attendance/early-leave',
            '/wali-kelas/grades',
        ]);

        $this->assertPages($this->wali, [
            "/wali-kelas/agenda/{$this->agenda->id}",
            "/wali-kelas/attendance/report/student/{$this->siswaUser->id}",
        ]);

        $this->assertPagesOk($this->wali, [
            '/wali-kelas/export/attendance',
            '/wali-kelas/export/attendance/pdf',
        ]);

        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/guru/dashboard')->assertForbidden();
        $this->get('/sekretaris/dashboard')->assertForbidden();
        $this->get('/siswa/dashboard')->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Wakasek
    // ------------------------------------------------------------------

    public function test_wakasek_can_access_all_wakasek_pages(): void
    {
        $this->assertPages($this->wakasek, [
            '/wakasek/dashboard',
            '/wakasek/profile',
            '/wakasek/curriculum',
            '/wakasek/curriculum/progress',
            '/wakasek/monitoring/agenda',
            '/wakasek/daily-attendance',
            '/wakasek/evaluation',
            '/wakasek/evaluation/report',
            '/wakasek/teacher-status',
            '/wakasek/teacher-status/report',
            '/wakasek/teaching',
            '/wakasek/reports/attendance',
        ]);

        $this->assertPages($this->wakasek, [
            "/wakasek/teaching/{$this->guru->id}",
            "/wakasek/teacher-status/{$this->teacherStatus->id}",
            "/wakasek/reports/attendance/student/{$this->siswaUser->id}",
        ]);

        $this->assertPagesOk($this->wakasek, [
            '/wakasek/teacher-status/report/csv',
            '/wakasek/teacher-status/report/pdf',
            '/wakasek/reports/export/excel',
            '/wakasek/reports/export/pdf',
            '/wakasek/export/teaching',
            '/wakasek/export/teaching/excel',
        ]);

        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/guru/dashboard')->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Sekretaris
    // ------------------------------------------------------------------

    public function test_sekretaris_can_access_all_sekretaris_pages(): void
    {
        $this->assertPages($this->sekretaris, [
            '/sekretaris/dashboard',
            '/sekretaris/profile',
            '/sekretaris/agenda',
            '/sekretaris/agenda/archive',
            '/sekretaris/agenda/create',
            '/sekretaris/attendance',
            '/sekretaris/attendance/report',
            '/sekretaris/daily-attendance',
        ]);

        $this->assertPages($this->sekretaris, [
            "/sekretaris/agenda/{$this->agenda->id}/edit",
            "/sekretaris/agenda/{$this->agenda->id}/preview",
            "/sekretaris/attendance/report/student/{$this->siswaUser->id}",
        ]);

        $this->assertPagesOk($this->sekretaris, [
            '/sekretaris/agenda/get-schedule-info?date=' . now()->format('Y-m-d'),
            '/sekretaris/print/agenda',
            '/sekretaris/print/attendance',
        ]);

        $this->get('/guru/dashboard')->assertForbidden();
        $this->get('/wali-kelas/dashboard')->assertForbidden();
        $this->get('/admin/dashboard')->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Siswa
    // ------------------------------------------------------------------

    public function test_siswa_can_access_all_siswa_pages(): void
    {
        $this->assertPages($this->siswaUser, [
            '/siswa/dashboard',
            '/siswa/profile',
            '/siswa/agenda',
            '/siswa/attendance',
            '/siswa/daily-attendance',
            '/siswa/daily-attendance/early-leave/history',
            '/siswa/grades',
            '/siswa/schedule',
            '/siswa/schedule/by-date?date=' . now()->format('Y-m-d'),
            '/siswa/schedule/change-week?current_date=' . now()->format('Y-m-d') . '&delta=0',
            '/siswa/schedule/today-date',
        ]);

        $this->assertPages($this->siswaUser, [
            "/siswa/agenda/{$this->agenda->id}/json",
            "/siswa/grades/{$this->studentGrade->id}",
        ]);

        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/guru/dashboard')->assertForbidden();
        $this->get('/wali-kelas/dashboard')->assertForbidden();
        $this->get('/sekretaris/dashboard')->assertForbidden();
        $this->get('/wakasek/dashboard')->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Super Admin
    // ------------------------------------------------------------------

    public function test_super_admin_can_access_all_super_admin_pages(): void
    {
        $this->assertPages($this->superAdmin, [
            '/super-admin/dashboard',
            '/super-admin/profile',
            '/super-admin/search?q=admin',
            '/super-admin/settings',
            '/super-admin/audit-logs',
            '/super-admin/admins',
            '/super-admin/admins/create',
            '/super-admin/admins/trash',
            '/super-admin/institutions',
            '/super-admin/institutions/create',
            '/super-admin/institutions/trash',
        ]);

        $this->assertPages($this->superAdmin, [
            "/super-admin/admins/{$this->admin->id}/edit",
            "/super-admin/institutions/1",
            "/super-admin/institutions/1/edit",
        ]);

        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/guru/dashboard')->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Perilaku umum
    // ------------------------------------------------------------------

    public function test_unauthenticated_user_is_redirected(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/guru/dashboard')->assertRedirect('/login');
        $this->get('/siswa/dashboard')->assertRedirect('/login');
        $this->get('/super-admin/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_redirects_authenticated_user_by_role(): void
    {
        $this->actingAs($this->guru)->get('/dashboard')->assertRedirect('/guru/dashboard');
        $this->actingAs($this->admin)->get('/dashboard')->assertRedirect('/admin/dashboard');
        $this->actingAs($this->wali)->get('/dashboard')->assertRedirect(route('wali-kelas.dashboard'));
    }
}