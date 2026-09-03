<?php

// database/seeders/E2eDatabaseSeeder.php
//
// Seeder khusus DATABASE E2E (Playwright). Hanya dipakai lewat:
//   php artisan db:seed --env=e2e --class=E2eDatabaseSeeder
//
// Beda dari DatabaseSeeder biasa:
// - Data minimal & deterministik (akun e2e.*@school.com).
// - Guru E2E PUNYA JADWAL Senin-Jumat → alur isi agenda bisa diuji.
// - Jam operasional di-override sampai 2099 → test bisa jalan kapan saja.
// - Lokasi sekolah nonaktif → tidak perlu geolocation.
// - Rate limit login dilonggarkan.
// Data development kamu tidak tersentuh (DB terpisah).

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\DailyAttendanceSetting;
use App\Models\Institution;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class E2eDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ---------------------------------------------------------------
        // Institusi
        // ---------------------------------------------------------------
        $institution = Institution::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'SMK E2E',
                'address' => 'Jl. Testing No. 1',
                'email' => 'e2e@school.com',
                'status' => 'active',
            ]
        );
        $instId = $institution->id;

        // ---------------------------------------------------------------
        // Roles & permissions
        // ---------------------------------------------------------------
        $permissions = [
            'manage users', 'manage classes', 'manage teachers', 'manage students',
            'manage subjects', 'manage schedule', 'manage agenda', 'manage attendance',
            'view dashboard', 'view reports', 'export data',
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [];
        foreach (['super_admin', 'admin', 'teacher', 'wali_kelas', 'sekretaris', 'siswa', 'wakasek'] as $roleName) {
            $roles[$roleName] = Role::firstOrCreate(['name' => $roleName]);
            $roles[$roleName]->syncPermissions(Permission::all());
        }

        // ---------------------------------------------------------------
        // Tahun ajaran
        // ---------------------------------------------------------------
        AcademicYear::updateOrCreate(
            ['institution_id' => $instId, 'name' => '2026/2027'],
            ['start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true, 'semester' => 'Ganjil']
        );
        AcademicYear::updateOrCreate(
            ['institution_id' => $instId, 'name' => '2025/2026'],
            ['start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => false, 'semester' => 'Genap']
        );

        // ---------------------------------------------------------------
        // Users (semua password: password)
        // ---------------------------------------------------------------
        $userDefs = [
            ['email' => 'superadmin@school.com', 'name' => 'Super Admin E2E', 'role' => 'super_admin'],
            ['email' => 'admin@school.com', 'name' => 'Admin E2E', 'role' => 'admin'],
            ['email' => 'e2e.guru@school.com', 'name' => 'Guru E2E', 'role' => 'teacher'],
            ['email' => 'e2e.walikelas@school.com', 'name' => 'Wali Kelas E2E', 'role' => 'wali_kelas'],
            ['email' => 'e2e.sekretaris@school.com', 'name' => 'Sekretaris E2E', 'role' => 'sekretaris'],
            ['email' => 'e2e.siswa@school.com', 'name' => 'Siswa E2E', 'role' => 'siswa'],
            ['email' => 'e2e.wakasek@school.com', 'name' => 'Wakasek E2E', 'role' => 'wakasek'],
            ['email' => 'e2e.guru.walikelas@school.com', 'name' => 'Guru Wali Kelas E2E', 'role' => 'teacher', 'extra_roles' => ['wali_kelas'], 'lookup_key' => 'guru_walikelas'],
            ['email' => 'e2e.guru.wakasek@school.com', 'name' => 'Guru Wakasek E2E', 'role' => 'teacher', 'extra_roles' => ['wakasek'], 'lookup_key' => 'guru_wakasek'],
        ];

        $userLookup = [];
        foreach ($userDefs as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'institution_id' => $instId,
                    'status' => 'active',
                ]
            );
            $user->syncRoles([$data['role']]);
            if (!empty($data['extra_roles'])) {
                $user->assignRole($data['extra_roles']);
            }
            $lookupKey = $data['lookup_key'] ?? $data['role'];
            $userLookup[$lookupKey] = $user;
        }

        // ---------------------------------------------------------------
        // Kelas
        // ---------------------------------------------------------------
        $class = Classes::updateOrCreate(
            ['institution_id' => $instId, 'name' => 'X RPL E2E'],
            [
                'major' => 'RPL',
                'grade_level' => 'X',
                'academic_year' => '2026/2027',
                'homeroom_teacher_id' => $userLookup['wali_kelas']->id,
                'capacity' => 30,
                'is_active' => true,
            ]
        );

        // Sekretaris & siswa berada di kelas ini (dashboard mereka butuh konteks kelas)
        $userLookup['sekretaris']->update(['class_id' => $class->id]);
        $userLookup['siswa']->update(['class_id' => $class->id]);

        // ---------------------------------------------------------------
        // Mapel (diampu guru E2E)
        // ---------------------------------------------------------------
        $subject = Subject::updateOrCreate(
            ['institution_id' => $instId, 'name' => 'Pemrograman Web E2E'],
            ['description' => 'Mapel untuk testing E2E', 'credit_hours' => 2]
        );
        $subject->teachers()->syncWithoutDetaching([$userLookup['teacher']->id]);

        // ---------------------------------------------------------------
        // Ruang
        // ---------------------------------------------------------------
        $room = Room::updateOrCreate(
            ['institution_id' => $instId, 'name' => 'Ruang E2E'],
            ['type' => 'Kelas', 'capacity' => 30, 'is_active' => true]
        );

        // ---------------------------------------------------------------
        // Jadwal guru E2E — Senin s.d. Jumat (07:00–08:00)
        // ---------------------------------------------------------------
        $academicYearId = AcademicYear::where('institution_id', $instId)->where('is_active', true)->value('id');

        foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
            Schedule::updateOrCreate(
                [
                    'teacher_id' => $userLookup['teacher']->id,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'day' => $day,
                ],
                [
                    'start_time' => '07:00:00',
                    'end_time' => '08:00:00',
                    'room' => $room->name,
                    'room_id' => $room->id,
                    'academic_year_id' => $academicYearId,
                    'institution_id' => $instId,
                ]
            );

            // Multi-role guru users (guru+waliKelas, guru+wakasek) also need schedules
            foreach (['guru_walikelas', 'guru_wakasek'] as $multiKey) {
                if (isset($userLookup[$multiKey])) {
                    Schedule::updateOrCreate(
                        [
                            'teacher_id' => $userLookup[$multiKey]->id,
                            'class_id' => $class->id,
                            'subject_id' => $subject->id,
                            'day' => $day,
                        ],
                        [
                            'start_time' => '09:00:00',
                            'end_time' => '10:00:00',
                            'room' => $room->name,
                            'room_id' => $room->id,
                            'academic_year_id' => $academicYearId,
                            'institution_id' => $instId,
                        ]
                    );
                }
            }
        }

        // ---------------------------------------------------------------
        // Settings penting (global)
        // ---------------------------------------------------------------
        // Bypass jam operasional → test bisa isi agenda kapan saja
        Setting::set('operational_override_until', '2099-12-31 23:59:59', 'general');
        Setting::set('operational_start_time', '06:30', 'general');
        Setting::set('operational_end_time', '23:59', 'general');
        Setting::set('operational_days', '1,2,3,4,5,6,7', 'general');

        // Setting absensi harian untuk institusi test: buka 24 jam, tanpa
        // foto wajib (foto tetap dikirim lewat kamera palsu di browser),
        // WhatsApp nonaktif (tidak ada panggilan keluar).
        DailyAttendanceSetting::forInstitution($instId)->update([
            'check_in_start' => '00:00:00',
            'check_in_deadline' => '23:59:59',
            'check_in_verification_deadline' => '23:59:59',
            'check_out_start' => '00:00:00',
            'check_out_tolerance_minutes' => 0,
            'late_tolerance_minutes' => 0,
            'require_check_in_photo' => false,
            'require_check_out_photo' => false,
            'whatsapp_enabled' => false,
        ]);

        // Lokasi sekolah nonaktif → tidak wajib geolocation
        Setting::set('school_location_enabled', '0', 'general');

        // Auth: bebas login pakai email
        Setting::set('auth_allow_login', '1', 'general');
        Setting::set('auth_login_email', '1', 'general');
        Setting::set('auth_login_username', '1', 'general');

        // Rate limit login dilonggarkan (test parallel)
        Setting::set('sec_max_login_attempts', '20', 'general');
        Setting::set('sec_lockout_duration', '1', 'general');

        Setting::set('school_name', 'SMK E2E', 'general');

        $this->command->info('E2E database seeded.');
        $this->command->info('  superadmin@school.com / password');
        $this->command->info('  admin@school.com / password');
        $this->command->info('  e2e.guru@school.com / password');
        $this->command->info('  e2e.walikelas@school.com / password');
        $this->command->info('  e2e.sekretaris@school.com / password');
        $this->command->info('  e2e.siswa@school.com / password');
        $this->command->info('  e2e.wakasek@school.com / password');
    }
}
