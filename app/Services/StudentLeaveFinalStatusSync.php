<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\StudentEarlyLeaveRequest;
use Carbon\Carbon;

class StudentLeaveFinalStatusSync
{
    public function syncApproved(StudentEarlyLeaveRequest $request): void
    {
        if ($request->status !== 'approved') {
            return;
        }

        $status = $this->finalStatusFor($request);
        if ($status === null) {
            return;
        }

        $request->loadMissing('student');

        $start = Carbon::parse($request->date);
        $end = Carbon::parse($request->effectiveEndDate());
        $note = $this->noteFor($request);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateString = $date->toDateString();

            $attendance = Attendance::withoutGlobalScope('academic_year')->firstOrNew([
                'student_id' => $request->student_id,
                'date' => $dateString,
            ]);

            $oldValues = $attendance->exists ? $attendance->only(['status', 'note', 'check_in_time']) : null;

            $attendance->fill([
                'class_id' => $request->class_id,
                'status' => $status,
                'note' => $note,
                'check_in_time' => in_array($status, ['present', 'late'], true)
                    ? $attendance->check_in_time
                    : null,
                'institution_id' => $request->institution_id ?: $request->student?->institution_id,
                'source' => 'manual',
            ])->save();

            AuditLogger::log(
                'student_leave_final_status_sync',
                'attendances',
                $attendance->id,
                $attendance->institution_id,
                $oldValues,
                [
                    'student_early_leave_request_id' => $request->id,
                    'date' => $dateString,
                    'status' => $status,
                    'note' => $note,
                ]
            );
        }
    }

    private function finalStatusFor(StudentEarlyLeaveRequest $request): ?string
    {
        if ($request->category === 'sakit') {
            return 'sick';
        }

        if ($request->isTidakHadirCategory()) {
            return 'excused';
        }

        if ($request->isDispenCategory()) {
            return 'present';
        }

        return null;
    }

    private function noteFor(StudentEarlyLeaveRequest $request): string
    {
        $lateLabel = $request->reviewed_at && $request->reviewed_at->toDateString() > $request->date->toDateString()
            ? ' Disetujui setelah tanggal mulai pengajuan.'
            : '';

        return trim('Sinkron dari pengajuan '.$request->categoryLabel().': '.$request->reason.$lateLabel);
    }
}
