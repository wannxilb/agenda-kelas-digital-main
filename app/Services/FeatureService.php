<?php

namespace App\Services;

use App\Models\Setting;

class FeatureService
{
    protected static array $features = [
        'agenda_harian' => [
            'key' => 'feature_agenda_harian',
            'label' => 'Agenda Harian',
            'desc' => 'Pencatatan agenda kelas harian',
            'default' => true,
        ],
        'attendance' => [
            'key' => 'feature_attendance',
            'label' => 'Presensi Siswa',
            'desc' => 'Pencatatan kehadiran siswa',
            'default' => true,
        ],
        'schedule' => [
            'key' => 'feature_schedule',
            'label' => 'Jadwal Pelajaran',
            'desc' => 'Manajemen jadwal mengajar',
            'default' => true,
        ],
        'rooms' => [
            'key' => 'feature_rooms',
            'label' => 'Manajemen Ruangan',
            'desc' => 'Pengelolaan data ruangan kelas',
            'default' => true,
        ],
        'export_pdf' => [
            'key' => 'feature_export_pdf',
            'label' => 'Export PDF',
            'desc' => 'Ekspor laporan ke format PDF',
            'default' => true,
        ],
        'export_excel' => [
            'key' => 'feature_export_excel',
            'label' => 'Export Excel',
            'desc' => 'Ekspor data ke format Excel',
            'default' => false,
        ],
        'import_data' => [
            'key' => 'feature_import_data',
            'label' => 'Import Data',
            'desc' => 'Impor data siswa/guru dari file',
            'default' => true,
        ],
        'nilai_tugas' => [
            'key' => 'feature_nilai_tugas',
            'label' => 'Nilai Tugas Siswa',
            'desc' => 'Nilai tugas yang dilihat siswa per mapel',
            'default' => true,
        ],
    ];

    public static function all(): array
    {
        return static::$features;
    }

    public static function isEnabled(string $feature, $institutionId = null): bool
    {
        $definition = static::$features[$feature] ?? null;

        if (!$definition) {
            return false;
        }

        try {
            return Setting::get($definition['key'], $definition['default'] ? '1' : '0', $institutionId) === '1';
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    public static function isDisabled(string $feature, $institutionId = null): bool
    {
        return !static::isEnabled($feature, $institutionId);
    }

    public static function get(string $feature): ?array
    {
        return static::$features[$feature] ?? null;
    }

    public static function getKey(string $feature): ?string
    {
        return static::$features[$feature]['key'] ?? null;
    }

    public static function routeMapping(): array
    {
        return [
            'agenda_harian' => [
                'prefixes' => ['agenda'],
                'roles' => ['sekretaris', 'guru', 'wali-kelas'],
            ],
            'attendance' => [
                'prefixes' => ['attendance', 'presensi'],
                'roles' => ['sekretaris', 'guru', 'wali-kelas', 'siswa', 'wakasek'],
            ],
            'schedule' => [
                'prefixes' => ['schedule', 'jadwal'],
                'roles' => ['admin', 'siswa'],
            ],
            'rooms' => [
                'prefixes' => ['rooms', 'ruangan'],
                'roles' => ['admin'],
            ],
            'export_pdf' => [
                'prefixes' => ['export'],
                'patterns' => ['pdf'],
            ],
            'export_excel' => [
                'prefixes' => ['export'],
                'patterns' => ['excel'],
            ],
            'import_data' => [
                'routes' => ['students.import', 'teachers.import'],
            ],
        ];
    }
}
