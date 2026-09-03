<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use App\Traits\HasAcademicYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDailyAttendance extends Model
{
    use BelongsToInstitution, HasAcademicYear, HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'date',
        'check_in_at',
        'check_in_photo',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_accuracy',
        'check_in_distance_meters',
        'check_in_ip_address',
        'check_in_user_agent',
        'check_in_device_fingerprint',
        'check_in_suspicious',
        'check_in_suspicious_reason',
        'check_in_status',
        'verification_method',
        'verified_by',
        'verified_at',
        'verification_note',
        'overridden_by',
        'overridden_at',
        'late_minutes',
        'check_out_at',
        'check_out_photo',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_accuracy',
        'check_out_distance_meters',
        'check_out_ip_address',
        'check_out_user_agent',
        'check_out_device_fingerprint',
        'check_out_suspicious',
        'check_out_suspicious_reason',
        'check_out_status',
        'early_leave_minutes',
        'check_out_verification_method',
        'check_out_verified_by',
        'check_out_verified_at',
        'check_out_verification_note',
        'academic_year_id',
        'institution_id',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'check_in_latitude' => 'float',
        'check_in_longitude' => 'float',
        'check_in_accuracy' => 'float',
        'check_in_distance_meters' => 'float',
        'check_in_suspicious' => 'boolean',
        'check_out_latitude' => 'float',
        'check_out_longitude' => 'float',
        'check_out_accuracy' => 'float',
        'check_out_distance_meters' => 'float',
        'check_out_suspicious' => 'boolean',
        'verified_at' => 'datetime',
        'overridden_at' => 'datetime',
        'check_out_verified_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function whatsappLogs()
    {
        return $this->hasMany(WhatsappNotificationLog::class);
    }

    public function corrections()
    {
        return $this->hasMany(StudentDailyAttendanceCorrection::class);
    }

    public function earlyLeaveRequests()
    {
        return $this->hasMany(StudentEarlyLeaveRequest::class, 'student_id', 'student_id')
            ->where('date', '<=', $this->date)
            ->whereRaw('COALESCE(date_end, date) >= ?', [$this->date]);
    }

    public function checkInPresentation(): array
    {
        return $this->attendancePresentation('check_in');
    }

    public function checkOutPresentation(): array
    {
        return $this->attendancePresentation('check_out');
    }

    public function attendancePresentation(string $event): array
    {
        $status = $event === 'check_in' ? $this->check_in_status : $this->check_out_status;
        $recordedAt = $event === 'check_in' ? $this->check_in_at : $this->check_out_at;
        $minutes = $event === 'check_in' ? (int) $this->late_minutes : (int) $this->early_leave_minutes;
        $suspicious = $event === 'check_in' ? (bool) $this->check_in_suspicious : (bool) $this->check_out_suspicious;
        $suspiciousReason = $event === 'check_in' ? $this->check_in_suspicious_reason : $this->check_out_suspicious_reason;
        $verificationMethod = $event === 'check_in' ? $this->verification_method : $this->check_out_verification_method;
        $badges = [];

        $addBadge = function (string $label, string $tone) use (&$badges): void {
            $badges[] = compact('label', 'tone');
        };

        switch ($status) {
            case 'alpha':
                $addBadge('Alpha (melebati batas)', 'bg-rose-50 text-rose-700 ring-rose-100');

                if ($minutes > 0) {
                    $addBadge('Terlambat '.$minutes.' menit', 'bg-rose-50 text-rose-700 ring-rose-100');
                }
                break;

            case 'late':
                $addBadge('Terlambat '.$minutes.' menit', 'bg-rose-50 text-rose-700 ring-rose-100');
                break;

            case 'teacher_assisted':
                $addBadge('Dibantu wali kelas', 'bg-blue-50 text-blue-700 ring-blue-100');

                if ($minutes > 0) {
                    $addBadge('Terlambat '.$minutes.' menit', 'bg-rose-50 text-rose-700 ring-rose-100');
                }
                break;

            case 'teacher_verified':
                if ($verificationMethod === 'teacher_assisted') {
                    $addBadge('Dibantu wali kelas', 'bg-blue-50 text-blue-700 ring-blue-100');
                } elseif ($verificationMethod === 'device_review') {
                    $addBadge('Disetujui verifikasi titip absen', 'bg-blue-50 text-blue-700 ring-blue-100');
                } elseif ($verificationMethod === 'late_review') {
                    $addBadge('Diubah ke terlambat oleh wali kelas', 'bg-blue-50 text-blue-700 ring-blue-100');
                } else {
                    $addBadge('Disetujui wali kelas', 'bg-blue-50 text-blue-700 ring-blue-100');
                }

                if ($minutes > 0) {
                    $addBadge('Terlambat '.$minutes.' menit', 'bg-rose-50 text-rose-700 ring-rose-100');
                }

                if ($suspicious) {
                    $addBadge('Titip / cek perangkat', 'bg-rose-50 text-rose-700 ring-rose-100');
                }
                break;

            case 'teacher_rejected':
                $addBadge('Ditolak wali kelas', 'bg-rose-50 text-rose-700 ring-rose-100');

                if ($suspicious) {
                    $addBadge('Titip / cek perangkat', 'bg-rose-50 text-rose-700 ring-rose-100');
                }
                break;

            case 'early':
                $addBadge('Pulang cepat '.$minutes.' menit', 'bg-amber-50 text-amber-700 ring-amber-100');
                break;

            case 'checked_out':
                $addBadge('Pulang', 'bg-emerald-50 text-emerald-700 ring-emerald-100');
                break;

            case 'early_with_permission':
                $addBadge('Pulang dengan izin', 'bg-amber-50 text-amber-700 ring-amber-100');
                break;

            case 'additional_activity':
                $addBadge('Dispensasi kegiatan', 'bg-blue-50 text-blue-700 ring-blue-100');
                break;

            case 'sick':
                $addBadge('Sakit', 'bg-rose-50 text-rose-700 ring-rose-100');
                break;

            case 'excused':
                $addBadge('Izin lainnya', 'bg-amber-50 text-amber-700 ring-amber-100');
                break;

            default:
                if ($event === 'check_in') {
                    $recordedAt
                        ? $addBadge('Masuk', 'bg-emerald-50 text-emerald-700 ring-emerald-100')
                        : $addBadge('Belum masuk', 'bg-gray-50 text-gray-500 ring-gray-100');
                } else {
                    $recordedAt
                        ? $addBadge('Pulang', 'bg-emerald-50 text-emerald-700 ring-emerald-100')
                        : $addBadge('Belum pulang', 'bg-gray-50 text-gray-500 ring-gray-100');
                }
                break;
        }

        if ($suspiciousReason && in_array($status, ['alpha', 'teacher_verified', 'teacher_rejected'], true)) {
            $badges[] = [
                'label' => $suspiciousReason,
                'tone' => 'bg-white text-gray-500 ring-gray-200',
            ];
        }

        return $badges;
    }
}
