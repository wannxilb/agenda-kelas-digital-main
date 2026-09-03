<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Attendance;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        DB::table('institutions')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'SMK Digital',
                'address' => 'Alamat Sekolah',
                'phone' => null,
                'email' => 'admin@school.com',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Create permissions if not exists
        $permissions = [
            'manage users', 'manage classes', 'manage teachers', 'manage students',
            'manage subjects', 'manage schedule', 'manage agenda', 'manage attendance',
            'view dashboard', 'view reports', 'export data'
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Create roles if not exists
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $studentRole = Role::firstOrCreate(['name' => 'siswa']);
        $sekretarisRole = Role::firstOrCreate(['name' => 'sekretaris']);
        $waliKelasRole = Role::firstOrCreate(['name' => 'wali_kelas']);
        $wakasekRole = Role::firstOrCreate(['name' => 'wakasek']);
        
        // Assign permissions to roles
        $superAdminRole->syncPermissions(Permission::all());
        $adminRole->syncPermissions(['manage classes', 'manage teachers', 'manage students', 'manage subjects', 'manage schedule', 'view dashboard']);
        $teacherRole->syncPermissions(['manage agenda', 'manage attendance', 'view dashboard', 'view reports']);
        $studentRole->syncPermissions(['view dashboard']);
        $sekretarisRole->syncPermissions(['manage agenda', 'manage attendance', 'view dashboard', 'view reports']);
        $waliKelasRole->syncPermissions(['manage attendance', 'view dashboard', 'view reports']);
        $wakasekRole->syncPermissions(['view dashboard', 'view reports', 'export data']);
        
        // Create/Update Super Admin User
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@school.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('super_admin');

        $admin = User::updateOrCreate(
            ['email' => 'admin@school.com'],
            [
                'name' => 'Admin Sekolah',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'institution_id' => 1,
                'status' => 'active',
            ]
        );
        $admin->assignRole('admin');
        
        $this->call(AcademicYearSeeder::class);
        $this->call(MajorSeeder::class);
        $this->call(RoomSeeder::class);
        $this->call(DKVClassSeeder::class);
        $this->call(DPBClassSeeder::class);
        $this->call(MPClassSeeder::class);
        $this->call(BDClassSeeder::class);
        $this->call(FilmClassSeeder::class);
        $this->call(RPLClassSeeder::class);
        $this->call(AKClassSeeder::class);
        $this->call(TeacherListSeeder::class);
        $this->call(RPLScheduleSeeder::class);

        $guru = User::updateOrCreate(
            ['email' => 'guru@school.com'],
            [
                'name' => 'Guru Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'institution_id' => 1,
                'status' => 'active',
            ]
        );
        $guru->assignRole('teacher');

        foreach (['Pemrograman Web', 'Basis Data'] as $subjectName) {
            $subject = Subject::firstOrCreate(
                ['name' => $subjectName, 'institution_id' => 1],
                [
                    'description' => $subjectName,
                    'credit_hours' => 2,
                    'institution_id' => 1,
                ]
            );
            $subject->teachers()->syncWithoutDetaching([$guru->id]);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Super Admin: superadmin@school.com / password');
    }
}
