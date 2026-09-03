<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create';
    protected $description = 'Create or reset admin and super admin users';

    public function handle()
    {
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
        Role::firstOrCreate(['name' => 'teacher']);
        Role::firstOrCreate(['name' => 'siswa']);
        Role::firstOrCreate(['name' => 'sekretaris']);
        Role::firstOrCreate(['name' => 'wali_kelas']);
        Role::firstOrCreate(['name' => 'wakasek']);

        $superAdminRole->syncPermissions(Permission::all());
        $adminRole->syncPermissions(['manage classes', 'manage teachers', 'manage students', 'manage subjects', 'manage schedule', 'view dashboard']);

        // Create/update Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@school.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->syncRoles(['super_admin']);
        $this->info('Super Admin: superadmin@school.com / password');

        // Create/update Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@school.com'],
            [
                'name' => 'Admin Sekolah',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);
        $this->info('Admin: admin@school.com / password');

        $this->info('Done! Users created/updated successfully.');

        return Command::SUCCESS;
    }
}
