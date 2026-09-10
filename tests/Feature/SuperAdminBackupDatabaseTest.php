<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SuperAdminBackupDatabaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected string $baseStoragePath;

    protected string $tempStorageBase;

    protected string $originalSqlitePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->superAdmin = User::where('email', 'superadmin@school.com')->firstOrFail();

        $this->baseStoragePath = $this->app->storagePath();
        $this->tempStorageBase = sys_get_temp_dir() . '/qa-backup-storage-' . uniqid();
        mkdir($this->tempStorageBase, 0755, true);
        $this->app->useStoragePath($this->tempStorageBase);

        $this->originalSqlitePath = config('database.connections.sqlite.database');
    }

    protected function tearDown(): void
    {
        config(['database.connections.sqlite.database' => $this->originalSqlitePath]);
        $this->app->useStoragePath($this->baseStoragePath);
        $this->deleteDirectory($this->tempStorageBase);

        parent::tearDown();
    }

    private function makeUser(string $role): User
    {
        $user = User::factory()->create([
            'email' => $role . '@school.test',
            'institution_id' => 1,
            'status' => 'active',
        ]);
        $user->syncRoles($role);

        return $user;
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);
        foreach ($items as $item) {
            if ($item->isDir()) {
                $this->deleteDirectory($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($dir);
    }

    public function test_unauthenticated_user_cannot_access_backup_routes(): void
    {
        $this->post(route('super-admin.settings.backup.database'))->assertRedirect('/login');
        $this->post(route('super-admin.settings.backup.files'))->assertRedirect('/login');
        $this->post(route('super-admin.settings.backup.restore'))->assertRedirect('/login');
        $this->get(route('super-admin.settings', ['tab' => 'backup']))->assertRedirect('/login');
    }

    public function test_non_superadmin_roles_get_403_on_backup_database(): void
    {
        foreach (['admin', 'teacher', 'wali_kelas', 'wakasek', 'sekretaris', 'siswa'] as $role) {
            $user = $this->makeUser($role);
            $this->actingAs($user)
                ->post(route('super-admin.settings.backup.database'))
                ->assertForbidden();
        }
    }

    public function test_non_superadmin_roles_get_403_on_backup_files(): void
    {
        foreach (['admin', 'teacher', 'wali_kelas', 'wakasek', 'sekretaris', 'siswa'] as $role) {
            $user = $this->makeUser($role);
            $this->actingAs($user)
                ->post(route('super-admin.settings.backup.files'))
                ->assertForbidden();
        }
    }

    public function test_non_superadmin_roles_get_403_on_restore_database(): void
    {
        foreach (['admin', 'teacher', 'wali_kelas', 'wakasek', 'sekretaris', 'siswa'] as $role) {
            $user = $this->makeUser($role);
            $this->actingAs($user)
                ->post(route('super-admin.settings.backup.restore'))
                ->assertForbidden();
        }
    }

    public function test_non_superadmin_cannot_view_settings_page(): void
    {
        $user = $this->makeUser('admin');
        $this->actingAs($user)
            ->get(route('super-admin.settings', ['tab' => 'backup']))
            ->assertForbidden();
    }

    public function test_superadmin_can_access_settings_backup_tab(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('super-admin.settings', ['tab' => 'backup']))
            ->assertOk()
            ->assertSee('Backup & Restore')
            ->assertSee('Backup Database');
    }

    public function test_superadmin_can_backup_sqlite_database(): void
    {
        $fakeSource = $this->tempStorageBase . '/fake-source.sqlite';
        file_put_contents($fakeSource, 'FAKE SQLITE DATA');
        config(['database.connections.sqlite.database' => $fakeSource]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.settings.backup.database'));

        $response->assertOk();
        $this->assertStringContainsString('FAKE SQLITE DATA', $response->streamedContent());
        $this->assertStringContainsString('.sqlite', $response->baseResponse->headers->get('content-disposition'));
    }

    public function test_backup_database_creates_backups_directory_if_missing(): void
    {
        $fakeSource = $this->tempStorageBase . '/fake-source.sqlite';
        file_put_contents($fakeSource, 'FAKE SQLITE DATA');
        config(['database.connections.sqlite.database' => $fakeSource]);

        $this->assertDirectoryDoesNotExist(storage_path('app/backups'));

        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.settings.backup.database'))
            ->assertOk();

        $this->assertDirectoryExists(storage_path('app/backups'));
    }

    public function test_backup_database_returns_error_when_sqlite_file_missing(): void
    {
        config(['database.connections.sqlite.database' => $this->tempStorageBase . '/missing.sqlite']);

        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.settings.backup.database'))
            ->assertRedirect(route('super-admin.settings', ['tab' => 'backup']))
            ->assertSessionHas('error');
    }

    public function test_superadmin_can_backup_files_as_zip(): void
    {
        $backupsDir = storage_path('app/backups');
        mkdir($backupsDir, 0755, true);
        file_put_contents(storage_path('app/qa-file.txt'), 'hello backup');

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.settings.backup.files'));

        $response->assertOk();
        $this->assertStringContainsString('zip', $response->baseResponse->headers->get('content-type') ?? '');
    }

    public function test_backup_zip_excludes_backups_folder_and_contains_files(): void
    {
        $backupsDir = storage_path('app/backups');
        mkdir($backupsDir, 0755, true);
        file_put_contents(storage_path('app/qa-file.txt'), 'hello backup');

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.settings.backup.files'));

        $zipPath = $this->tempStorageBase . '/inspect.zip';
        file_put_contents($zipPath, $response->streamedContent());

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($zipPath) === true);

        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = $zip->getNameIndex($i);
        }
        $zip->close();

        $this->assertContains('qa-file.txt', $names);
        foreach ($names as $name) {
            $this->assertStringNotContainsString('backups', $name);
        }
    }

    public function test_superadmin_can_restore_sqlite_database(): void
    {
        $targetDb = $this->tempStorageBase . '/restore-target.sqlite';
        file_put_contents($targetDb, 'OLD DATA');
        config(['database.connections.sqlite.database' => $targetDb]);

        $backupPath = $this->tempStorageBase . '/restore-source.sql';
        file_put_contents($backupPath, 'RESTORED DATA');
        $uploaded = new UploadedFile($backupPath, 'restore.sql', 'application/octet-stream', null, true);

        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.settings.backup.restore'), ['backup_file' => $uploaded])
            ->assertRedirect(route('super-admin.settings', ['tab' => 'backup']))
            ->assertSessionHas('success');

        $this->assertSame('RESTORED DATA', file_get_contents($targetDb));
    }

    public function test_restore_without_file_returns_validation_error(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.settings.backup.restore'))
            ->assertSessionHasErrors('backup_file');
    }

    public function test_backup_files_returns_zip_even_when_storage_is_empty(): void
    {
        $backupsDir = storage_path('app/backups');
        mkdir($backupsDir, 0755, true);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.settings.backup.files'));

        $response->assertOk();
        $this->assertNotEmpty($response->streamedContent());
    }
}