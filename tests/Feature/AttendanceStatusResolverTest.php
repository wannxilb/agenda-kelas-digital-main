<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Institution;
use App\Models\Setting;
use App\Models\StudentDailyAttendance;
use App\Models\StudentEarlyLeaveRequest;
use App\Models\User;
use App\Services\AttendanceStatusResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceStatusResolverTest extends TestCase
{
    use RefreshDatabase;

    private AttendanceStatusResolver $resolver;

    private int $institutionId;

    private Classes $class;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = app(AttendanceStatusResolver::class);

        $institution = Institution::create(['name' => 'SMK Test', 'status' => 'active']);
        $this->institutionId = $institution->id;

        AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
            'institution_id' => $this->institutionId,
        ]);

        Role::create(['name' => 'siswa']);

        Role::create(['name' => 'wali_kelas']);
        $waliKelas = User::factory()->create([
            'institution_id' => $this->institutionId,
            'status' => 'active',
        ]);
        $waliKelas->assignRole('wali_kelas');

        $this->class = Classes::create([
            'name' => 'X RPL 1',
            'major' => 'RPL',
            'grade_level' => 'X',
            'academic_year' => '2026/2027',
            'homeroom_teacher_id' => $waliKelas->id,
            'capacity' => 30,
            'is_active' => true,
            'institution_id' => $this->institutionId,
        ]);

        $this->student = User::factory()->create([
            'institution_id' => $this->institutionId,
            'class_id' => $this->class->id,
            'status' => 'active',
        ]);
        $this->student->assignRole('siswa');
    }

    public function test_returns_present_when_checked_in_on_time(): void
    {
        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => '2026-08-03 06:55:00',
            'check_in_status' => 'on_time',
        ]);

        $this->assertEquals('present', $this->resolver->resolve($this->student->id, '2026-08-03'));
    }

    public function test_returns_late_when_checked_in_late(): void
    {
        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => '2026-08-03 07:15:00',
            'check_in_status' => 'late',
        ]);

        $this->assertEquals('late', $this->resolver->resolve($this->student->id, '2026-08-03'));
    }

    public function test_returns_absent_when_no_attendance_and_no_request(): void
    {
        $this->assertEquals('absent', $this->resolver->resolve($this->student->id, '2026-08-03'));
    }

    public function test_returns_sick_when_sakit_request_approved(): void
    {
        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'sakit',
            'reason' => 'Sakit demam',
            'status' => 'approved',
        ]);

        $this->assertEquals('sick', $this->resolver->resolve($this->student->id, '2026-08-03'));
    }

    public function test_returns_not_yet_on_non_operational_day_when_overdue(): void
    {
        $this->assertEquals('not_yet', $this->resolver->resolve($this->student->id, '2026-08-08'));
    }

    public function test_returns_not_yet_when_overdue_on_non_operational_day(): void
    {
        $this->assertEquals('not_yet', $this->resolver->deriveStatus(null, null, true, '2026-08-08', false));
        $this->assertEquals('absent', $this->resolver->deriveStatus(null, null, true, '2026-08-08'));
    }

    public function test_returns_absent_on_weekend_when_override_active(): void
    {
        Setting::set('operational_override_until', '2026-12-31', 'general', $this->institutionId);

        $this->assertEquals('absent', $this->resolver->resolve($this->student->id, '2026-08-08'));
    }

    public function test_returns_excused_when_izin_lainnya_approved(): void
    {
        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'izin_lainnya',
            'reason' => 'Urusan keluarga',
            'status' => 'approved',
        ]);

        $this->assertEquals('excused', $this->resolver->resolve($this->student->id, '2026-08-03'));
    }

    public function test_returns_present_when_dispen_lomba_approved(): void
    {
        Carbon::setTestNow('2026-08-03 08:00:00');

        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'date_end' => '2026-08-03',
            'category' => 'lomba',
            'reason' => 'Lomba OSN',
            'status' => 'approved',
        ]);

        $this->assertEquals('present', $this->resolver->resolve($this->student->id, '2026-08-03'));

        Carbon::setTestNow();
    }

    public function test_returns_present_when_dispen_kegiatan_sekolah_approved(): void
    {
        Carbon::setTestNow('2026-08-03 08:00:00');

        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'kegiatan_sekolah',
            'reason' => 'Upacara bendera',
            'status' => 'approved',
        ]);

        $this->assertEquals('present', $this->resolver->resolve($this->student->id, '2026-08-03'));

        Carbon::setTestNow();
    }

    public function test_returns_absent_when_request_not_approved(): void
    {
        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'sakit',
            'reason' => 'Sakit demam',
            'status' => 'pending',
        ]);

        $this->assertEquals('absent', $this->resolver->resolve($this->student->id, '2026-08-03'));
    }

    public function test_returns_present_when_pulang_cepat_but_checked_in(): void
    {
        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'category' => 'pulang_dijemput',
            'reason' => 'Dijemput orang tua',
            'status' => 'approved',
        ]);

        StudentDailyAttendance::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-03',
            'check_in_at' => '2026-08-03 06:55:00',
            'check_in_status' => 'on_time',
        ]);

        $this->assertEquals('present', $this->resolver->resolve($this->student->id, '2026-08-03'));
    }

    public function test_multi_day_request_covers_middle_date(): void
    {
        Carbon::setTestNow('2026-08-06 07:30:00');

        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-04',
            'date_end' => '2026-08-06',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'approved',
        ]);

        foreach (['2026-08-04', '2026-08-05', '2026-08-06'] as $coveredDate) {
            StudentDailyAttendance::create([
                'student_id' => $this->student->id,
                'class_id' => $this->class->id,
                'institution_id' => $this->institutionId,
                'date' => $coveredDate,
                'check_in_at' => $coveredDate.' 07:00:00',
                'check_in_status' => 'on_time',
            ]);
        }

        $this->assertEquals('present', $this->resolver->resolve($this->student->id, '2026-08-04'));
        $this->assertEquals('present', $this->resolver->resolve($this->student->id, '2026-08-05'));
        $this->assertEquals('present', $this->resolver->resolve($this->student->id, '2026-08-06'));

        Carbon::setTestNow();
    }

    public function test_multi_day_request_does_not_cover_date_outside_range(): void
    {
        Carbon::setTestNow('2026-08-06 07:30:00');

        StudentEarlyLeaveRequest::create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'institution_id' => $this->institutionId,
            'date' => '2026-08-04',
            'date_end' => '2026-08-05',
            'category' => 'lomba',
            'reason' => 'Lomba LKS',
            'status' => 'approved',
        ]);

        $this->assertEquals('not_yet', $this->resolver->resolve($this->student->id, '2026-08-06'));

        Carbon::setTestNow();
    }

    public function test_derive_status_method_directly(): void
    {
        $this->assertEquals('not_yet', $this->resolver->deriveStatus(null, null));
        $this->assertEquals('absent', $this->resolver->deriveStatus(null, null, true));

        $sickRequest = new StudentEarlyLeaveRequest(['category' => 'sakit']);
        $this->assertEquals('sick', $this->resolver->deriveStatus(null, $sickRequest));

        $izinRequest = new StudentEarlyLeaveRequest(['category' => 'izin_lainnya']);
        $this->assertEquals('excused', $this->resolver->deriveStatus(null, $izinRequest, true));

        $lombaRequest = new StudentEarlyLeaveRequest(['category' => 'lomba']);
        $this->assertEquals('present', $this->resolver->deriveStatus(null, $lombaRequest));

        $lateAttendance = new StudentDailyAttendance(['check_in_status' => 'late', 'check_in_at' => '07:10:00']);
        $this->assertEquals('late', $this->resolver->deriveStatus($lateAttendance, null));

        $approvedLateAttendance = new StudentDailyAttendance(['check_in_status' => 'teacher_verified', 'check_in_at' => '11:10:00', 'late_minutes' => 70]);
        $this->assertEquals('late', $this->resolver->deriveStatus($approvedLateAttendance, null));

        $alphaAttendance = new StudentDailyAttendance(['check_in_status' => 'alpha', 'check_in_at' => '11:10:00', 'late_minutes' => 70]);
        $this->assertEquals('absent', $this->resolver->deriveStatus($alphaAttendance, null));

        $rejectedAttendance = new StudentDailyAttendance(['check_in_status' => 'teacher_rejected', 'check_in_at' => '11:10:00', 'late_minutes' => 70]);
        $this->assertEquals('absent', $this->resolver->deriveStatus($rejectedAttendance, null));

        $onTimeAttendance = new StudentDailyAttendance(['check_in_status' => 'on_time', 'check_in_at' => '06:55:00']);
        $this->assertEquals('present', $this->resolver->deriveStatus($onTimeAttendance, null));
    }
}
