<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use App\Traits\HasAcademicYear;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class TeacherStatus extends Model
{
    use BelongsToInstitution, HasAcademicYear, HasFactory;

    public const TYPES = [
        'izin' => 'Izin',
        'sakit' => 'Sakit',
        'tugas_luar' => 'Tugas Luar',
    ];

    public const WORKFLOW_STATUSES = [
        'pending',
        'approved',
        'rejected',
        'cancelled',
    ];

    protected $fillable = [
        'teacher_id',
        'type',
        'status',
        'date',
        'date_end',
        'start_time',
        'end_time',
        'note',
        'attachment',
        'rejection_reason',
        'approver_id',
        'processed_at',
        'cancelled_at',
        'cancelled_by',
        'substitute_teacher_id',
        'cancellation_reason',
        'academic_year_id',
        'institution_id',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'date_end' => 'date:Y-m-d',
        'processed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function substituteTeacher()
    {
        return $this->belongsTo(User::class, 'substitute_teacher_id');
    }

    // ------------------------------------------------------------------
    // Helper status
    // ------------------------------------------------------------------

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', (string) $this->type));
    }

    public function statusLabel(): string
    {
        return [
            'pending' => 'Pending',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ][$this->status] ?? ucfirst($this->status);
    }

    // ------------------------------------------------------------------
    // Scope
    // ------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    // ------------------------------------------------------------------
    // Rentang tanggal (pola sama dengan StudentEarlyLeaveRequest)
    // ------------------------------------------------------------------

    public function effectiveEndDate(): Carbon
    {
        return $this->date_end ?: $this->date;
    }

    public function coversDate(string $date): bool
    {
        $date = Carbon::parse($date)->toDateString();

        return $this->date->toDateString() <= $date
            && $date <= $this->effectiveEndDate()->toDateString();
    }

    /**
     * Cek apakah guru punya record pending/approved yang rentangnya tumpang
     * tindih dengan rentang yang diajukan. Record rejected/cancelled tidak
     * menghalangi. $exceptId dipakai saat update (abaikan record itu sendiri).
     */
    public static function isOverlapping(int $teacherId, string $start, ?string $end = null, ?int $exceptId = null): bool
    {
        $start = Carbon::parse($start)->toDateString();
        $end = $end ? Carbon::parse($end)->toDateString() : $start;

        return self::query()
            ->where('teacher_id', $teacherId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('date', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('date_end')
                    ->where('date', '>=', $start)
                    ->orWhere(function ($q2) use ($start) {
                        $q2->whereNotNull('date_end')->where('date_end', '>=', $start);
                    });
            })
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists();
    }
}
