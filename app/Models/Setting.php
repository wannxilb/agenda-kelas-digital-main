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
     * Kegiatan rutin sekolah (misal: Senin & Jumat 45 menit).
     * Disimpan sebagai JSON array di setting 'recurring_activities'.
     *
     * @return array<int, array{id: string, name: string, start_time: string, duration: int, days: string[]}>
     */
    public static function recurringActivities(): array
    {
        $raw = self::get('recurring_activities', null);

        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * Nilai mentah recurring_activities untuk diisi ke hidden input.
     */
    public static function recurringActivitiesRaw(): string
    {
        $raw = self::get('recurring_activities', null);

        return (is_string($raw) && trim($raw) !== '') ? $raw : '[]';
    }

    /**
     * Daftar week_type yang tampil pada tanggal tertentu.
     *
     * Mode 'normal' -> hanya jadwal 'semua' (setiap minggu, tanpa pembedaan
     *                  ganjil/genap). Jadwal ganjil/genap diabaikan.
     * Mode 'block'  -> hanya jadwal sesuai minggu ke-berapa dari titik tetap:
     *                  minggu ganjil (ke-1,3,5,...) -> 'ganjil'
     *                  minggu genap (ke-2,4,6,...)  -> 'genap'.
     *
     * Titik tetap diambil dari awal tahun ajaran aktif (start_date), dibulatkan
     * ke Senin di minggu awal tersebut. Karena setiap kalender minggu (Senin-Minggu)
     * selalu jadi satu paritas, satu minggu pelajaran tidak pernah terbelah meski
     * membentang lintas bulan.
     *
     * @return string[]
     */
    public static function scheduleWeekTypesForDate(\Carbon\Carbon $date): array
    {
        if (self::scheduleMode() === 'normal') {
            return ['semua'];
        }

        $anchor = self::scheduleWeekAnchor($date);

        $weekNumber = (int) floor($anchor->diffInDays($date->copy()->startOfDay()) / 7) + 1;

        return ($weekNumber % 2 === 1) ? ['ganjil'] : ['genap'];
    }

    /**
     * Titik tetap (Senin) untuk menomori minggu bergantian ganjil/genap.
     *
     * Prioritas:
     *  1. start_date tahun ajaran yang menaungi tanggal tsb (cari berdasar
     *     rentang start_date..end_date, yang paling baru duluan).
     *  2. start_date tahun ajaran aktif (bila tak ada yang menaungi).
     *  3. Batas semester: 1 Juli untuk semester ganjil (Bulan >= 7) dan
     *     1 Januari untuk semester genap (Bulan < 7).
     *
     * Selalu dibulatkan ke Senin di minggu awal. Karena batas semester selalu
     * dipakai sebagai "pagar bawah", hitungan ganjil/genap otomatis restart tiap
     * pergantian semester: semester genap dihitung ulang dari 1 Januari,
     * semester ganjil dari 1 Juli.
     */
    private static function scheduleWeekAnchor(\Carbon\Carbon $date): \Carbon\Carbon
    {
        $institutionId = auth()->hasUser() ? auth()->user()->institution_id : null;

        // 1) Tahun ajaran/semester yang menaungi tanggal ini (paling baru duluan).
        $covering = AcademicYear::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->orderByDesc('start_date')
            ->first();

        $candidate = $covering?->start_date;

        // 2) Bila tak ada yang menaungi, coba tahun ajaran aktif.
        if ($candidate === null) {
            $activeYear = AcademicYear::query()
                ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
                ->where('is_active', true)
                ->orderByDesc('id')
                ->first();

            $candidate = $activeYear?->start_date;
        }

        // 3) Pagar bawah semester: 1 Juli (ganjil) atau 1 Januari (genap).
        $semesterBoundary = $date->month >= 7
            ? $date->copy()->month(7)->startOfMonth()
            : $date->copy()->month(1)->startOfMonth();

        // Pakai start_date tahun ajaran hanya jika masuk akal:
        // sudah dimulai (<= $date) dan tidak lebih lama dari pagar semester.
        $anchor = ($candidate && $candidate->lte($date) && $candidate->gte($semesterBoundary))
            ? $candidate
            : $semesterBoundary;

        return $anchor->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
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
