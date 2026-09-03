<?php

namespace App\Services;

use App\Models\StudentDailyAttendanceCorrection;

class AttendanceCorrectionApplier
{
    private const STATUS_MAP = [
        'sakit' => 'sick',
        'izin_lainnya' => 'early_with_permission',
        'izin_kegiatan' => 'additional_activity',
    ];

    public function apply(StudentDailyAttendanceCorrection $correction): void
    {
        $status = self::STATUS_MAP[$correction->requested_status] ?? null;
        if (!$status) {
            return;
        }

        $attendance = $correction->attendance()->firstOrFail();

        $field = $correction->target_event === 'check_in' ? 'check_in_status' : 'check_out_status';

        $attendance->update([$field => $status]);

        app(DailyAttendancePresensiSync::class)->sync($attendance->fresh());
    }
}
