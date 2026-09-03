<?php

namespace App\Services;

use App\Models\TeacherStatus;
use App\Models\User;
use App\Support\ImageCompressor;
use App\Support\TeacherStatusWhatsappNotifier;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TeacherStatusService
{
    /**
     * Guru mengajukan izin/tugas luar → status pending.
     */
    public function create(User $teacher, array $data, ?UploadedFile $attachment = null): TeacherStatus
    {
        $start = Carbon::parse($data['date'])->toDateString();
        $end = ! empty($data['date_end'])
            ? Carbon::parse($data['date_end'])->toDateString()
            : null;

        if (TeacherStatus::isOverlapping($teacher->id, $start, $end)) {
            throw ValidationException::withMessages([
                'date' => 'Anda sudah memiliki pengajuan izin/sakit/tugas luar yang tumpang tindih pada rentang tersebut.',
            ]);
        }

        $status = TeacherStatus::create([
            'teacher_id' => $teacher->id,
            'type' => $data['type'],
            'status' => 'pending',
            'date' => $start,
            'date_end' => $end,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'note' => $data['note'] ?? null,
            'attachment' => $this->storeAttachment($attachment),
            'institution_id' => $teacher->institution_id,
        ]);

        app(TeacherStatusWhatsappNotifier::class)->notifySubmission($status);

        return $status;
    }

    /**
     * Edit pengajuan — hanya status pending dan milik guru itu sendiri.
     */
    public function update(TeacherStatus $status, User $teacher, array $data, ?UploadedFile $attachment = null): TeacherStatus
    {
        if ($status->teacher_id !== $teacher->id || ! $status->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Pengajuan hanya dapat diubah saat status pending.',
            ]);
        }

        $start = Carbon::parse($data['date'])->toDateString();
        $end = ! empty($data['date_end'])
            ? Carbon::parse($data['date_end'])->toDateString()
            : null;

        if (TeacherStatus::isOverlapping($teacher->id, $start, $end, $status->id)) {
            throw ValidationException::withMessages([
                'date' => 'Anda sudah memiliki pengajuan izin/sakit/tugas luar yang tumpang tindih pada rentang tersebut.',
            ]);
        }

        $payload = [
            'type' => $data['type'],
            'date' => $start,
            'date_end' => $end,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'note' => $data['note'] ?? null,
        ];

        if ($attachment) {
            if ($status->attachment) {
                Storage::disk('public')->delete($status->attachment);
            }
            $payload['attachment'] = $this->storeAttachment($attachment);
        }

        $status->update($payload);

        app(TeacherStatusWhatsappNotifier::class)->notifyUpdated($status);

        return $status;
    }

    /**
     * Guru menarik pengajuannya sendiri — hanya saat pending & belum lewat tanggal.
     */
    public function withdraw(TeacherStatus $status, User $teacher): TeacherStatus
    {
        if ($status->teacher_id !== $teacher->id || ! $status->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Pengajuan tidak dapat ditarik pada status saat ini.',
            ]);
        }

        if (Carbon::parse($status->effectiveEndDate())->lt(Carbon::today())) {
            throw ValidationException::withMessages([
                'date' => 'Pengajuan sudah melewati tanggal dan tidak dapat ditarik.',
            ]);
        }

        $this->transition($status, 'pending', [
            'status' => 'cancelled',
            'cancelled_by' => $teacher->id,
            'cancelled_at' => now(),
            'cancellation_reason' => 'Ditarik oleh guru',
        ]);

        return $status;
    }

    /**
     * Wakasek menyetujui pengajuan pending.
     */
    public function approve(TeacherStatus $status, User $approver, ?int $substituteTeacherId = null): TeacherStatus
    {
        if ($substituteTeacherId) {
            $substitute = User::role('teacher')
                ->where('id', $substituteTeacherId)
                ->where('institution_id', $status->institution_id)
                ->where('id', '!=', $status->teacher_id)
                ->first();

            if (! $substitute) {
                throw ValidationException::withMessages([
                    'substitute_teacher_id' => 'Guru pengganti tidak valid (harus guru di institusi yang sama dan bukan guru yang bersangkutan).',
                ]);
            }
        }

        $this->transition($status, 'pending', [
            'status' => 'approved',
            'approver_id' => $approver->id,
            'processed_at' => now(),
            'substitute_teacher_id' => $substituteTeacherId,
        ]);

        app(TeacherStatusWhatsappNotifier::class)->notifyApproved($status);

        return $status;
    }

    /**
     * Wakasek menolak pengajuan pending (alasan wajib).
     */
    public function reject(TeacherStatus $status, User $approver, string $reason): TeacherStatus
    {
        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'rejection_reason' => 'Alasan penolakan wajib diisi.',
            ]);
        }

        $this->transition($status, 'pending', [
            'status' => 'rejected',
            'approver_id' => $approver->id,
            'processed_at' => now(),
            'rejection_reason' => trim($reason),
        ]);

        app(TeacherStatusWhatsappNotifier::class)->notifyRejected($status);

        return $status;
    }

    /**
     * Wakasek membatalkan pengajuan yang sudah approved (alasan wajib).
     */
    public function cancel(TeacherStatus $status, User $approver, string $reason): TeacherStatus
    {
        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'cancellation_reason' => 'Alasan pembatalan wajib diisi.',
            ]);
        }

        $this->transition($status, 'approved', [
            'status' => 'cancelled',
            'approver_id' => $approver->id,
            'processed_at' => now(),
            'cancelled_by' => $approver->id,
            'cancelled_at' => now(),
            'cancellation_reason' => trim($reason),
        ]);

        app(TeacherStatusWhatsappNotifier::class)->notifyCancelled($status);

        return $status;
    }

    /**
     * Auto-expire pending yang tanggalnya sudah lewat (scheduler harian).
     *
     * @return int jumlah record yang dikadaluarsakan
     */
    public function expirePending(): int
    {
        $today = Carbon::today()->toDateString();

        $expired = TeacherStatus::query()
            ->where('status', 'pending')
            ->get()
            ->filter(fn (TeacherStatus $status) => Carbon::parse($status->effectiveEndDate())->lt(Carbon::parse($today)))
            ->values();

        foreach ($expired as $status) {
            $this->transition($status, 'pending', [
                'status' => 'cancelled',
                'cancelled_by' => null,
                'cancelled_at' => now(),
                'cancellation_reason' => 'Otomatis: pengajuan melewati tanggal',
            ]);
        }

        return $expired->count();
    }

    /**
     * Simpan lampiran ke disk public + kompres gambar (JPEG/PNG) supaya
     * ukurannya wajar untuk surat izin/dinas (desain §7.6).
     */
    private function storeAttachment(?UploadedFile $attachment): ?string
    {
        if (! $attachment) {
            return null;
        }

        $path = $attachment->store('teacher-statuses', 'public');

        if (in_array($attachment->getClientMimeType(), ['image/jpeg', 'image/png'])) {
            ImageCompressor::compress($path);
        }

        return $path;
    }

    /**
     * Rekap bulanan izin/tugas luar per guru (hanya approved).
     *
     * "Total hari" = diffInDays(date, date_end) + 1 per record (desain §12).
     *
     * @return Collection<int, array{teacher: ?User, izin_days: int, tugas_days: int, total_days: int}>
     */
    public function monthlyReport(?int $month = null, ?int $year = null): Collection
    {
        $month = (int) ($month ?: now()->month);
        $year = (int) ($year ?: now()->year);

        $records = TeacherStatus::with('teacher')
            ->approved()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        return $records->groupBy('teacher_id')->map(function ($items) {
            $izinDays = 0;
            $sakitDays = 0;
            $tugasDays = 0;

            foreach ($items as $item) {
                $days = $this->daysFor($item);
                if ($item->type === 'tugas_luar') {
                    $tugasDays += $days;
                } elseif ($item->type === 'sakit') {
                    $sakitDays += $days;
                } else {
                    $izinDays += $days;
                }
            }

            return [
                'teacher' => $items->first()->teacher,
                'izin_days' => $izinDays,
                'sakit_days' => $sakitDays,
                'tugas_days' => $tugasDays,
                'total_days' => $izinDays + $sakitDays + $tugasDays,
            ];
        })->sortByDesc('total_days')->values();
    }

    /**
     * Rekap bulanan izin/tugas luar UNTUK SATU guru (dashboard guru).
     *
     * @return array{izin_days: int, tugas_days: int, total_days: int, approved: int, pending: int}
     */
    public function teacherMonthlySummary(User $teacher, ?int $month = null, ?int $year = null): array
    {
        $month = (int) ($month ?: now()->month);
        $year = (int) ($year ?: now()->year);

        $records = TeacherStatus::where('teacher_id', $teacher->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $summary = [
            'izin_days' => 0,
            'sakit_days' => 0,
            'tugas_days' => 0,
            'total_days' => 0,
            'approved' => 0,
            'pending' => 0,
        ];

        foreach ($records as $item) {
            if ($item->isPending()) {
                $summary['pending']++;

                continue;
            }

            if (! $item->isApproved()) {
                continue;
            }

            $summary['approved']++;
            $days = $this->daysFor($item);

            if ($item->type === 'tugas_luar') {
                $summary['tugas_days'] += $days;
            } elseif ($item->type === 'sakit') {
                $summary['sakit_days'] += $days;
            } else {
                $summary['izin_days'] += $days;
            }
        }

        $summary['total_days'] = $summary['izin_days'] + $summary['sakit_days'] + $summary['tugas_days'];

        return $summary;
    }

    /**
     * Status guru yang sudah disetujui dan overlap dengan rentang tanggal.
     *
     * @param  array<int>|null  $teacherIds
     * @return Collection<int, TeacherStatus>
     */
    public function approvedForDateRange(string|Carbon $start, string|Carbon|null $end = null, ?array $teacherIds = null): Collection
    {
        $start = Carbon::parse($start)->toDateString();
        $end = Carbon::parse($end ?: $start)->toDateString();

        if ($teacherIds !== null && $teacherIds === []) {
            return collect();
        }

        return TeacherStatus::query()
            ->with('substituteTeacher:id,name')
            ->where('status', 'approved')
            ->when($teacherIds, fn ($query) => $query->whereIn('teacher_id', $teacherIds))
            ->where('date', '<=', $end)
            ->where(function ($query) use ($start) {
                $query->where(function ($q) use ($start) {
                    $q->whereNull('date_end')->where('date', '>=', $start);
                })->orWhere(function ($q) use ($start) {
                    $q->whereNotNull('date_end')->where('date_end', '>=', $start);
                });
            })
            ->get();
    }

    /**
     * Jumlah hari efektif satu record: diffInDays(date, date_end) + 1.
     */
    private function daysFor(TeacherStatus $status): int
    {
        return Carbon::parse($status->date)->diffInDays(Carbon::parse($status->effectiveEndDate())) + 1;
    }

    /**
     * Update atomik kondisional: hanya berhasil kalau status saat ini masih
     * $from — mencegah race condition dua wakasek approve bersamaan.
     */
    private function transition(TeacherStatus $status, string $from, array $payload): void
    {
        $updated = TeacherStatus::query()
            ->whereKey($status->id)
            ->where('status', $from)
            ->update($payload);

        if (! $updated) {
            throw ValidationException::withMessages([
                'status' => 'Status pengajuan tidak dapat diubah.',
            ]);
        }

        $status->refresh();
    }
}
