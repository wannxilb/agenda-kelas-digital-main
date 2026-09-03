<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    /**
     * Safety net TTL (detik). Cache selalu di-invalidasi saat setting ditulis
     * lewat event model saved/deleted, jadi nilai selalu segar.
     */
    private const CACHE_TTL = 300;

    /**
     * Slot jam pelajaran default untuk kalender jadwal.
     * Baris dengan label ditampilkan sebagai istirahat.
     */
    private const DEFAULT_SCHEDULE_TIME_SLOTS = [
        ['time' => '06:30', 'label' => null],
        ['time' => '07:15', 'label' => null],
        ['time' => '08:00', 'label' => null],
        ['time' => '08:45', 'label' => null],
        ['time' => '09:30', 'label' => 'Istirahat'],
        ['time' => '09:45', 'label' => null],
        ['time' => '10:30', 'label' => null],
        ['time' => '11:15', 'label' => null],
        ['time' => '12:00', 'label' => 'Istirahat & ISHOMA'],
        ['time' => '12:45', 'label' => null],
        ['time' => '13:30', 'label' => null],
        ['time' => '14:00', 'label' => null],
        ['time' => '14:15', 'label' => null],
        ['time' => '15:00', 'label' => null],
        ['time' => '15:30', 'label' => null],
        ['time' => '15:45', 'label' => null],
    ];

    protected $fillable = ['key', 'value', 'group', 'institution_id'];

    /**
     * Get a setting value. Checks institution-specific first, then global.
     */
    public static function get($key, $default = null, $institutionId = null)
    {
        if ($institutionId === null && auth()->hasUser()) {
            $institutionId = auth()->user()->institution_id;
        }

        // Check institution-specific setting first
        if ($institutionId) {
            $value = self::cachedValue($key, $institutionId);
            if ($value !== null) {
                return $value;
            }
        }

        // Fall back to global setting
        $value = self::cachedValue($key, null);

        return $value ?? $default;
    }

    /**
     * Set a setting value. Auto-scopes to current user's institution for admin role.
     */
    public static function set($key, $value, $group = 'general', $institutionId = null)
    {
        if ($institutionId === null && auth()->hasUser() && auth()->user()->hasRole('admin')) {
            $institutionId = auth()->user()->institution_id;
        }

        $existing = self::where('key', $key)
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->when(! $institutionId, fn ($q) => $q->whereNull('institution_id'))
            ->first();

        if ($existing) {
            $existing->update(['value' => $value, 'group' => $group]);

            return $existing;
        }

        return self::create([
            'key' => $key,
            'value' => $value,
            'group' => $group,
            'institution_id' => $institutionId,
        ]);
    }

    /**
     * Get all settings merged: institution-specific overrides global.
     */
    public static function allForInstitution($institutionId = null)
    {
        if ($institutionId === null && auth()->hasUser()) {
            $institutionId = auth()->user()->institution_id;
        }

        $globalSettings = self::whereNull('institution_id')->pluck('value', 'key');

        if ($institutionId) {
            $instSettings = self::where('institution_id', $institutionId)->pluck('value', 'key');

            return $globalSettings->merge($instSettings);
        }

        return $globalSettings;
    }

    /**
     * Slot jam pelajaran untuk kalender jadwal, dari setting schedule_time_slots.
     * Format value: satu baris per slot, "HH:MM" untuk jam pelajaran, atau
     * "HH:MM|Label" untuk baris istirahat.
     *
     * @return array<int, array{time: string, label: string|null}>
     */
    public static function scheduleTimeSlots(): array
    {
        $raw = self::get('schedule_time_slots', null);

        if (! is_string($raw) || trim($raw) === '') {
            return self::DEFAULT_SCHEDULE_TIME_SLOTS;
        }

        $slots = [];
        foreach (preg_split('/\r?\n/', $raw) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            [$time, $label] = array_pad(explode('|', $line, 2), 2, null);
            $slots[] = [
                'time' => trim($time),
                'label' => is_string($label) && trim($label) !== '' ? trim($label) : null,
            ];
        }

        return $slots !== [] ? $slots : self::DEFAULT_SCHEDULE_TIME_SLOTS;
    }

    /**
     * Nilai mentah schedule_time_slots untuk diisi ke textarea pengaturan.
     * Jatuh ke default bila belum pernah disimpan.
     */
    public static function scheduleTimeSlotsRaw(): string
    {
        $raw = self::get('schedule_time_slots', null);

        if (is_string($raw) && trim($raw) !== '') {
            return $raw;
        }

        return implode("\n", array_map(
            fn (array $slot) => $slot['time'].($slot['label'] !== null ? '|'.$slot['label'] : ''),
            self::DEFAULT_SCHEDULE_TIME_SLOTS
        ));
    }

    /**
     * Mode penjadwalan:
     *  - 'block'  : jadwal ganjil/genap hanya tampil di minggu yang sesuai (sistem block bergantian per minggu).
     *  - 'normal' : semua jadwal tampil setiap minggu (tidak ada pembedaan ganjil/genap).
     *
     * Default 'block' agar perilaku filter minggu tetap berlaku bila belum pernah diatur.
     */
    public static function scheduleMode(): string
    {
        $mode = self::get('schedule_mode', 'block');

        return in_array($mode, ['block', 'normal'], true) ? $mode : 'block';
    }

    /**
     * Daftar week_type yang tampil pada tanggal tertentu.
     *
     * Mode 'normal' -> semua minggu ikut tampil (tidak bedakan ganjil/genap).
     * Mode 'block'  -> 'semua' + sesuai minggu ke-berapa dalam bulan berjalan:
     *                  minggu ke-1,3,5 (ganjil) atau ke-2,4 (genap).
     *
     * @return string[]
     */
    public static function scheduleWeekTypesForDate(\Carbon\Carbon $date): array
    {
        if (self::scheduleMode() === 'normal') {
            return ['semua', 'ganjil', 'genap'];
        }

        $weekNumber = (int) floor(($date->day - 1) / 7) + 1;

        if ($weekNumber % 2 === 1) {
            return ['semua', 'ganjil'];
        }

        return ['semua', 'genap'];
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Baca satu nilai setting dari cache, per scope (institution/global).
     *
     * Nilai dibungkus array supaya hasil "tidak ketemu" (null) ikut tersimpan:
     * Cache::remember memperlakukan nilai null yang tersimpan sebagai miss,
     * jadi tanpa bungkus ini query akan diulang terus tiap request.
     */
    private static function cachedValue(string $key, ?int $institutionId): ?string
    {
        $entry = Cache::remember(
            self::cacheKey($key, $institutionId),
            self::CACHE_TTL,
            fn () => [
                'value' => self::where('key', $key)
                    ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
                    ->when($institutionId === null, fn ($q) => $q->whereNull('institution_id'))
                    ->value('value'),
            ]
        );

        return $entry['value'] ?? null;
    }

    private static function cacheKey(string $key, ?int $institutionId): string
    {
        return 'setting.raw:v1:'.($institutionId ?? 'global').':'.$key;
    }

    /**
     * Invalidasi cache setiap kali setting ditulis/dihapus — termasuk jalur
     * yang memakai updateOrCreate/delete langsung (mis. Super Admin).
     */
    protected static function booted(): void
    {
        static::saved(function (self $setting) {
            Cache::forget(self::cacheKey($setting->key, $setting->institution_id));
        });

        static::deleted(function (self $setting) {
            Cache::forget(self::cacheKey($setting->key, $setting->institution_id));
        });
    }
}
