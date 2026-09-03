<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class DailyAttendanceSetting extends Model
{
    use BelongsToInstitution, HasFactory;

    /**
     * Safety net TTL (detik). Cache selalu di-invalidasi saat model disimpan
     * atau dihapus lewat event model, jadi nilai selalu segar.
     */
    private const CACHE_TTL = 300;

    protected $fillable = [
        'institution_id',
        'check_in_start',
        'check_in_deadline',
        'check_in_verification_deadline',
        'check_out_start',
        'check_out_override_date',
        'check_out_override_time',
        'check_out_tolerance_minutes',
        'late_tolerance_minutes',
        'require_check_in_photo',
        'require_check_out_photo',
        'whatsapp_enabled',
        'whatsapp_mode',
        'whatsapp_provider',
        'whatsapp_api_url',
        'whatsapp_token',
        'whatsapp_country_code',
        'check_in_message_template',
        'check_out_message_template',
    ];

    protected $casts = [
        'require_check_in_photo' => 'boolean',
        'require_check_out_photo' => 'boolean',
        'whatsapp_enabled' => 'boolean',
    ];

    public static function forInstitution(?int $institutionId): self
    {
        return Cache::remember(
            'daily-attendance-setting:v1:'.($institutionId ?? 'global'),
            self::CACHE_TTL,
            function () use ($institutionId) {
                return self::firstOrCreate(
                    ['institution_id' => $institutionId],
                    [
                        'check_in_start' => '05:00:00',
                        'check_in_deadline' => '06:30:00',
                        'check_in_verification_deadline' => '08:00:00',
                        'check_out_start' => '14:30:00',
                        'check_out_tolerance_minutes' => 0,
                        'late_tolerance_minutes' => 0,
                        'require_check_in_photo' => true,
                        'require_check_out_photo' => true,
                        'whatsapp_enabled' => false,
                        'whatsapp_mode' => 'lengkap',
                        'whatsapp_provider' => 'fonnte',
                        'whatsapp_api_url' => 'https://api.fonnte.com/send',
                        'whatsapp_country_code' => '62',
                        'check_in_message_template' => 'Ananda {student} telah masuk sekolah pukul {time}. Status: {status}.',
                        'check_out_message_template' => 'Ananda {student} telah pulang sekolah pukul {time}. Status: {status}.',
                    ]
                );
            }
        );
    }

    /**
     * Apakah notifikasi WhatsApp berjalan dalam mode hemat
     * (hanya keterlambatan, pulang cepat/kegiatan, dan keputusan izin;
     * tidak mengirim untuk absen masuk tepat waktu dan pulang normal).
     */
    public function isEconomyWhatsappMode(): bool
    {
        return ($this->whatsapp_mode ?? 'lengkap') === 'hemat';
    }

    /**
     * Apakah notifikasi WhatsApp hanya dikirim untuk siswa tidak hadir
     * (lewat command `attendance:notify-absent`); semua notif masuk/pulang
     * dan keputusan izin tidak dikirim.
     */
    public function isOnlyAbsentWhatsappMode(): bool
    {
        return ($this->whatsapp_mode ?? 'lengkap') === 'hanya_absen';
    }

    /**
     * Invalidasi cache setiap kali setting absensi ditulis/dihapus.
     */
    protected static function booted(): void
    {
        static::saved(function (self $setting) {
            Cache::forget('daily-attendance-setting:v1:'.($setting->institution_id ?? 'global'));
        });

        static::deleted(function (self $setting) {
            Cache::forget('daily-attendance-setting:v1:'.($setting->institution_id ?? 'global'));
        });
    }
}
