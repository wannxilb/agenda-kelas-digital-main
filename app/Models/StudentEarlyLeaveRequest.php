<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class StudentEarlyLeaveRequest extends Model
{
    use BelongsToInstitution, HasFactory;

    public const CATEGORY_OPTIONS = [
        'sakit' => 'Sakit',
        'izin' => 'Izin / Berhalangan hadir',
        'lomba' => 'Lomba / Event sekolah',
        'kegiatan_sekolah' => 'Kegiatan sekolah',
        'kegiatan_tambahan' => 'Kegiatan tambahan / Ekstrakurikuler',
        'pulang_sakit' => 'Kondisi kesehatan / tiba-tiba sakit',
        'pulang_awal' => 'Keperluan pribadi / keluarga',
        'pulang_darurat' => 'Keadaan darurat',
        'pulang_lainnya' => 'Lainnya',
    ];

    public const LEGACY_CATEGORIES = [
        'izin_lainnya' => 'Izin lainnya',
        'izin_jemput' => 'Dijemput untuk izin',
        'keluar_sementara' => 'Keluar sementara',
        'dipanggil_guru_bk' => 'Dipanggil guru / BK',
        'pulang_dijemput' => 'Pulang dijemput',
        'pulang_kegiatan' => 'Pulang untuk kegiatan / acara',
    ];

    public const CATEGORY_GROUPS = [
        'ketidakhadiran' => 'Ketidakhadiran',
        'dispensasi' => 'Dispensasi',
        'pulang_cepat' => 'Pulang cepat',
    ];

    public const CATEGORY_GROUP_MAP = [
        'sakit' => 'ketidakhadiran',
        'izin' => 'ketidakhadiran',
        'izin_lainnya' => 'ketidakhadiran',
        'lomba' => 'dispensasi',
        'kegiatan_sekolah' => 'dispensasi',
        'kegiatan_tambahan' => 'dispensasi',
        'pulang_sakit' => 'pulang_cepat',
        'pulang_awal' => 'pulang_cepat',
        'pulang_darurat' => 'pulang_cepat',
        'pulang_lainnya' => 'pulang_cepat',
    ];

    public const CATEGORY_GROUP_DESCRIPTIONS = [
        'ketidakhadiran' => 'Tidak masuk sekolah seharian',
        'dispensasi' => 'Punya kegiatan, jadi tidak mengikuti pelajaran',
        'pulang_cepat' => 'Masuk sekolah, lalu pulang sebelum jam pulang',
    ];

    public const CATEGORY_DEFINITIONS = [
        'sakit' => [
            'description' => 'Tidak masuk karena sakit atau kondisi medis.',
            'evidence' => 'required',
            'multi_day' => true,
            'needs_check_in' => false,
            'auto_checkout' => false,
            'remote' => false,
        ],
        'izin' => [
            'description' => 'Tidak masuk karena keperluan pribadi / keluarga.',
            'evidence' => 'required',
            'multi_day' => true,
            'needs_check_in' => false,
            'auto_checkout' => false,
            'remote' => false,
        ],
        'izin_lainnya' => [
            'description' => 'Tidak masuk karena keperluan pribadi / keluarga.',
            'evidence' => 'required',
            'multi_day' => true,
            'needs_check_in' => false,
            'auto_checkout' => false,
            'remote' => false,
        ],
        'lomba' => [
            'description' => 'Mewakili sekolah untuk lomba / event di luar sekolah.',
            'evidence' => 'required',
            'multi_day' => true,
            'needs_check_in' => true,
            'auto_checkout' => true,
            'remote' => true,
        ],
        'kegiatan_sekolah' => [
            'description' => 'Ikut kegiatan resmi yang diadakan sekolah (di dalam atau luar sekolah).',
            'evidence' => 'required',
            'multi_day' => true,
            'needs_check_in' => true,
            'auto_checkout' => true,
            'remote' => true,
        ],
        'kegiatan_tambahan' => [
            'description' => 'Ikut latihan / kegiatan ekstrakurikuler atau kegiatan lainnya di sela pelajaran.',
            'evidence' => 'optional',
            'multi_day' => false,
            'needs_check_in' => true,
            'auto_checkout' => false,
            'remote' => false,
        ],
        'pulang_sakit' => [
            'description' => 'Tiba-tiba sakit saat di sekolah dan harus pulang.',
            'evidence' => 'optional',
            'multi_day' => false,
            'needs_check_in' => true,
            'auto_checkout' => false,
            'remote' => false,
        ],
        'pulang_awal' => [
            'description' => 'Pulang lebih awal untuk keperluan pribadi / keluarga.',
            'evidence' => 'optional',
            'multi_day' => false,
            'needs_check_in' => true,
            'auto_checkout' => false,
            'remote' => false,
        ],
        'pulang_darurat' => [
            'description' => 'Ada keadaan darurat yang mengharuskan pulang.',
            'evidence' => 'optional',
            'multi_day' => false,
            'needs_check_in' => true,
            'auto_checkout' => false,
            'remote' => false,
        ],
        'pulang_lainnya' => [
            'description' => 'Alasan pulang cepat lainnya.',
            'evidence' => 'optional',
            'multi_day' => false,
            'needs_check_in' => true,
            'auto_checkout' => false,
            'remote' => false,
        ],
    ];

    public const ACTIVITY_CATEGORIES = [
        'kegiatan_sekolah',
        'lomba',
    ];

    public const PULANG_CEPAT_CATEGORIES = [
        'pulang_awal',
        'pulang_sakit',
        'pulang_dijemput',
        'pulang_darurat',
        'pulang_lainnya',
    ];

    public const SINGLE_DAY_CATEGORIES = [
        'kegiatan_tambahan',
        'pulang_awal',
        'pulang_sakit',
        'pulang_dijemput',
        'pulang_darurat',
        'pulang_lainnya',
    ];

    public const MAX_FUTURE_REQUEST_DAYS = 30;

    public const EVIDENCE_REQUIRED_CATEGORIES = [
        'kegiatan_sekolah',
        'lomba',
        'sakit',
        'izin',
        'izin_lainnya',
    ];

    protected $fillable = [
        'student_id', 'class_id', 'date', 'date_end', 'category', 'reason', 'evidence_path', 'evidence_mime',
        'status', 'reviewed_by', 'reviewed_at', 'reviewer_note', 'subject_teacher_name', 'activity_name',
        'activity_start_time', 'activity_end_time',
        'institution_id',
    ];

    protected $casts = ['date' => 'date:Y-m-d', 'date_end' => 'date:Y-m-d', 'reviewed_at' => 'datetime'];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function categoryOptions(): array
    {
        return self::CATEGORY_OPTIONS;
    }

    public static function categoryGroups(): array
    {
        return self::CATEGORY_GROUPS;
    }

    public static function categoryGroupDescriptions(): array
    {
        return self::CATEGORY_GROUP_DESCRIPTIONS;
    }

    public static function categoryDefinitions(): array
    {
        return self::CATEGORY_DEFINITIONS;
    }

    public static function categoryDefinition(?string $category): ?array
    {
        return $category ? (self::CATEGORY_DEFINITIONS[$category] ?? null) : null;
    }

    public static function groupedCategoryOptions(): array
    {
        $grouped = [];
        foreach (self::CATEGORY_OPTIONS as $value => $label) {
            $group = self::CATEGORY_GROUP_MAP[$value] ?? null;
            if ($group) {
                $grouped[$group][$value] = $label;
            }
        }

        return $grouped;
    }

    public static function categoriesForGroup(string $group): array
    {
        return array_keys(array_filter(self::CATEGORY_GROUP_MAP, fn (string $mapGroup) => $mapGroup === $group));
    }

    public static function allowedCategories(): array
    {
        return array_merge(self::CATEGORY_OPTIONS, self::LEGACY_CATEGORIES);
    }

    public function categoryLabel(): string
    {
        return self::allowedCategories()[$this->category] ?? str_replace('_', ' ', $this->category);
    }

    public function categoryGroup(): ?string
    {
        return self::CATEGORY_GROUP_MAP[$this->category] ?? null;
    }

    public function isActivityCategory(): bool
    {
        return in_array($this->category, self::ACTIVITY_CATEGORIES, true);
    }

    public function isPulangCepatCategory(): bool
    {
        return in_array($this->category, ['pulang_awal', 'pulang_sakit', 'pulang_dijemput', 'pulang_darurat', 'pulang_lainnya'], true);
    }

    public function isTidakHadirCategory(): bool
    {
        return in_array($this->category, ['sakit', 'izin', 'izin_lainnya'], true);
    }

    public function isDispenCategory(): bool
    {
        return in_array($this->category, ['lomba', 'kegiatan_sekolah'], true);
    }

    public function isMultiDay(): bool
    {
        return $this->date_end !== null && $this->date_end->toDateString() !== $this->date->toDateString();
    }

    public function isSingleDayCategory(): bool
    {
        return in_array($this->category, self::SINGLE_DAY_CATEGORIES, true);
    }

    public function isRemoteEligibleCategory(): bool
    {
        return $this->isDispenCategory();
    }

    public function effectiveEndDate(): Carbon
    {
        return $this->date_end ?: $this->date;
    }

    public function coversDate(string $date): bool
    {
        $date = Carbon::parse($date)->toDateString();

        return $this->date->toDateString() <= $date && $date <= $this->effectiveEndDate()->toDateString();
    }

    public static function hasRemoteApprovedForDate(int $studentId, string $date): bool
    {
        return self::where('student_id', $studentId)
            ->where('status', 'approved')
            ->where('date', '<=', $date)
            ->whereRaw('COALESCE(date_end, date) >= ?', [$date])
            ->get()
            ->contains(fn (self $request) => $request->isRemoteEligibleCategory());
    }

    public static function evidenceRequiredCategories(): array
    {
        return self::EVIDENCE_REQUIRED_CATEGORIES;
    }

    public static function requiresEvidenceFor(?string $category): bool
    {
        return in_array($category, self::EVIDENCE_REQUIRED_CATEGORIES, true);
    }
}
