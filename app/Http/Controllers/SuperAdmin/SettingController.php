<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditLogger;
use App\Services\FeatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * Display the settings page with the active tab.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'general');
        $settings = Setting::whereNull('institution_id')->pluck('value', 'key');

        // System info for "About" tab
        $systemInfo = [
            'laravel_version' => app()->version(),
            'php_version' => phpversion(),
            'database' => config('database.default'),
            'environment' => app()->environment(),
            'storage_path' => storage_path(),
            'server_os' => php_uname('s') . ' ' . php_uname('r'),
        ];

        return view('super_admin.settings.index', compact('settings', 'tab', 'systemInfo'));
    }

    /**
     * Update settings for a specific group/tab.
     */
    public function update(Request $request)
    {
        $tab = $request->input('_tab', 'general');

        // Collect old values for audit log
        $oldValues = [];
        $newValues = [];

        foreach ($request->except(['_token', '_tab']) as $key => $value) {
            $existing = Setting::where('key', $key)->whereNull('institution_id')->first();
            $oldValues[$key] = $existing?->value;
            $newValues[$key] = $value;

            Setting::updateOrCreate(
                ['key' => $key, 'institution_id' => null],
                ['value' => $value, 'group' => $tab]
            );
        }

        // Handle checkboxes (they are not sent when unchecked)
        $checkboxGroups = [
            'authentication' => [
                'auth_allow_login', 'auth_login_email',
                'auth_login_username', 'auth_force_password_change', 'auth_auto_logout',
            ],
            'features' => array_map(fn($f) => $f['key'], FeatureService::all()),
            'audit' => [
                'audit_log_login', 'audit_log_crud', 'audit_log_error',
            ],
        ];

        if (isset($checkboxGroups[$tab])) {
            foreach ($checkboxGroups[$tab] as $checkboxKey) {
                if (!$request->has($checkboxKey)) {
                    $existing = Setting::where('key', $checkboxKey)->whereNull('institution_id')->first();
                    $oldValues[$checkboxKey] = $existing?->value;
                    $newValues[$checkboxKey] = '0';

                    Setting::updateOrCreate(
                        ['key' => $checkboxKey, 'institution_id' => null],
                        ['value' => '0', 'group' => $tab]
                    );
                }
            }
        }

        AuditLogger::log(
            'update_settings',
            'settings',
            null,
            null,
            $oldValues,
            $newValues
        );

        return redirect()
            ->route('super-admin.settings', ['tab' => $tab])
            ->with('success', __('Pengaturan berhasil diperbarui.'));
    }

    /**
     * Toggle maintenance mode.
     */
    public function toggleMaintenance(Request $request)
    {
        $enabled = $request->input('maintenance_enabled', '0');
        $message = $request->input('maintenance_message', 'Sistem sedang dalam pemeliharaan.');

        Setting::set('maintenance_enabled', $enabled, 'maintenance');
        Setting::set('maintenance_message', $message, 'maintenance');

        AuditLogger::log(
            $enabled === '1' ? 'enable_maintenance' : 'disable_maintenance',
            'settings',
            null,
            null,
            [],
            ['maintenance_enabled' => $enabled, 'maintenance_message' => $message]
        );

        $status = $enabled === '1' ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('super-admin.settings', ['tab' => 'maintenance'])
            ->with('success', __('Mode maintenance berhasil :status.', ['status' => $status]));
    }

    /**
     * Send a test email to verify SMTP config.
     */
    public function testEmail(Request $request)
    {
        $request->validate(['test_email_address' => 'required|email']);

        try {
            \Illuminate\Support\Facades\Mail::raw(
                __('Ini adalah email percobaan dari Agenda Kelas Digital. Konfigurasi SMTP Anda berhasil!'),
                function ($message) use ($request) {
                    $message->to($request->test_email_address)
                            ->subject(__('Test Email - Agenda Kelas Digital'));
                }
            );

            return redirect()
                ->route('super-admin.settings', ['tab' => 'email'])
                ->with('success', __('Email percobaan berhasil dikirim ke :email', ['email' => $request->test_email_address]));
        } catch (\Exception $e) {
            return redirect()
                ->route('super-admin.settings', ['tab' => 'email'])
                ->with('error', __('Gagal mengirim email: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Backup Database
     */
    public function backupDatabase()
    {
        $driver = config('database.default');

        if ($driver === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            if (!file_exists($dbPath)) {
                return redirect()->route('super-admin.settings', ['tab' => 'backup'])
                    ->with('error', __('File database SQLite tidak ditemukan.'));
            }
            $filename = 'backup-' . date('Y-m-d_H-i-s') . '.sqlite';
            $path = storage_path('app/backups/' . $filename);
            if (!is_dir(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }
            copy($dbPath, $path);
            return response()->download($path)->deleteFileAfterSend(true);
        }

        $filename = 'backup-' . date('Y-m-d_H-i-s') . '.sql';
        $path = storage_path('app/backups/' . $filename);
        if (!is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');
        $dbHost = env('DB_HOST');
        $dbPort = env('DB_PORT', '5432');

        putenv("PGPASSWORD=" . $dbPass);

        $command = sprintf(
            'pg_dump --host=%s --port=%s --username=%s --dbname=%s -f %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbName),
            escapeshellarg($path)
        );

        exec($command . ' 2>&1', $output, $returnVar);

        if ($returnVar !== 0) {
            Log::error('PostgreSQL backup failed:', [
                'command' => 'pg_dump ...', // Don't log password
                'output' => $output,
                'returnVar' => $returnVar
            ]);
            return redirect()
                ->route('super-admin.settings', ['tab' => 'backup'])
                ->with('error', __('Gagal melakukan backup database PostgreSQL. Periksa log sistem.'));
        }

        return response()->download($path)->deleteFileAfterSend(true);
    }

    /**
     * Backup Files
     */
    public function backupFiles()
    {
        $zip = new \ZipArchive();
        $filename = 'backup-files-' . date('Y-m-d_H-i-s') . '.zip';
        if (!is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }
        $path = storage_path('app/backups/' . $filename);

        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()
                ->route('super-admin.settings', ['tab' => 'backup'])
                ->with('error', __('Gagal membuat file ZIP.'));
        }

        // Add storage/app directory files (excluding the backups folder itself)
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(storage_path('app')),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            // Skip directories and the backup file itself
            if (!$file->isDir() && strpos($file->getRealPath(), storage_path('app/backups')) === false) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen(storage_path('app')) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        // ZipArchive (terutama di Windows) tidak membuat/menghapus file fisik
        // ketika tidak ada entry yang ditambahkan. Tulis ZIP kosong yang valid
        // agar download tidak error 500 pada storage yang kosong.
        if (!file_exists($path)) {
            file_put_contents($path, "\x50\x4b\x05\x06" . str_repeat("\x00", 18));
        }

        return response()->download($path)->deleteFileAfterSend(true);
    }

    /**
     * Restore Database
     */
    public function restoreDatabase(Request $request)
    {
        $request->validate(['backup_file' => 'required|file']);

        $driver = config('database.default');
        $file = $request->file('backup_file');
        $path = $file->getRealPath();

        if ($driver === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            copy($path, $dbPath);
            return redirect()
                ->route('super-admin.settings', ['tab' => 'backup'])
                ->with('success', __('Database berhasil di-restore.'));
        }

        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');
        $dbHost = env('DB_HOST');
        $dbPort = env('DB_PORT', '5432');

        putenv("PGPASSWORD=" . $dbPass);

        $command = sprintf(
            'psql --host=%s --port=%s --username=%s --dbname=%s -f %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbName),
            escapeshellarg($path)
        );

        exec($command . ' 2>&1', $output, $returnVar);

        if ($returnVar !== 0) {
            Log::error('PostgreSQL restore failed:', [
                'command' => 'psql ...',
                'output' => $output,
                'returnVar' => $returnVar
            ]);
            return redirect()
                ->route('super-admin.settings', ['tab' => 'backup'])
                ->with('error', __('Gagal melakukan restore database PostgreSQL. Periksa log sistem.'));
        }

        return redirect()
            ->route('super-admin.settings', ['tab' => 'backup'])
            ->with('success', __('Database berhasil di-restore.'));
    }
}
