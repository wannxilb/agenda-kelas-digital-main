<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\StudentDailyAttendance;
use App\Models\StudentEarlyLeaveRequest;
use Carbon\Carbon;

class DispenCheckInEnforcer
{
    /**
     * Flag approved dispen dates (hari ini & sebelumnya) yang tidak memiliki
     * bukti absen masuk sebagai 'unknown' (perlu verifikasi), dan pastikan
     * tanggal dengan bukti absen tetap 'present'.
     *
     * Baris yang pernah diubah sumber lain (note tidak berawalan "Sinkron dari
     * pengajuan") dilewati supaya tidak menimpa koreksi manual.
     */
    public function flagWithoutCheckIn(): int
    {
        $today = now()->toDateString();
        $requests = StudentEarlyLeaveRequest::where('status', 'approved')
            ->where('date', '<=', $today)
            ->get()
            ->filter(fn (StudentEarlyLeaveRequest $request) => $request->isDispenCategory());

        $flagged = 0;

        foreach ($requests as $request) {
            $end = Carbon::parse($request->effectiveEndDate())->min(Carbon::parse($today));

            for ($date = Carbon::parse($request->date); $date->lte($end); $date->addDay()) {
                $dateString = $date->toDateString();
                $operationalEnd = Setting::get('operational_end_time', '16:00', $request->institution_id);
                if (now()->lessThan(Carbon::parse($dateString.' '.$operationalEnd))) {
                    continue;
                }

                $hasCheckIn = StudentDailyAttendance::where('student_id', $request->student_id)
                    ->whereDate('date', $dateString)
                    ->whereNotNull('check_in_at')
                    ->exists();

                $status = $hasCheckIn ? 'present' : 'unknown';

                $attendance = Attendance::withoutGlobalScope('academic_year')->firstOrNew([
                    'student_id' => $request->student_id,
                    'date' => $dateString,
                ]);

                if ($attendance->exists && ! str_starts_with($attendance->note ?? '', 'Sinkron dari pengajuan')) {
                    continue;
                }

                if ($attendance->exists && $attendance->status === $status) {
                    continue;
                }

                $note = 'Sinkron dari pengajuan '.$request->categoryLabel().': '.$request->reason
                    .($hasCheckIn ? '' : ' (Tanpa bukti absen masuk)');

                $attendance->fill([
                    'class_id' => $request->class_id,
                    'status' => $status,
                    'note' => $note,
                    'check_in_time' => null,
                    'institution_id' => $request->institution_id ?: $request->student?->institution_id,
                    'source' => 'manual',
                ])->save();

                if ($status === 'unknown') {
                    $flagged++;
                }
            }
        }

        return $flagged;
    }
}
