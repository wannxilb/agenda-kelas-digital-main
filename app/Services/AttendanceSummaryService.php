<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\DailyAttendanceSetting;
use App\Models\StudentDailyAttendance;
use App\Models\StudentEarlyLeaveRequest;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSummaryService
{
    public function __construct(private AttendanceStatusResolver $resolver) {}

    /**
     * Resolve presensi status per student per date within a range.
     *
     * Final presensi (Attendance) takes precedence. Digital kehadiran and
     * approved izin/dispen are used only as initial/fallback data.
     *
     * @param  array<int, int>  $studentIds
     * @param  array<int, int>|null  $academicYearIds
     * @return array<int, array<string, string>> student_id => [date => status]
     */
    public function resolveForRange(array $studentIds, string $startDate, string $endDate, ?array $academicYearIds = null): array
    {
        if (empty($studentIds)) {
            return [];
        }

        $manual = Attendance::withoutGlobalScope('academic_year')
            ->whereIn('student_id', $studentIds)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->when($academicYearIds, fn ($q) => $q->whereIn('academic_year_id', $academicYearIds))
            ->get();

        $digital = StudentDailyAttendance::withoutGlobalScope('academic_year')
            ->whereIn('student_id', $studentIds)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->get();

        $requests = StudentEarlyLeaveRequest::whereIn('student_id', $studentIds)
            ->where('status', 'approved')
            ->whereDate('date', '<=', $endDate)
            ->where(function ($q) use ($startDate) {
                $q->whereDate('date_end', '>=', $startDate)->orWhereNull('date_end');
            })
            ->get();

        $manualMap = [];
        foreach ($manual as $att) {
            $manualMap[$att->student_id][$att->date->toDateString()] = $att->status;
        }

        $digitalMap = [];
        $institutionIds = [];
        foreach ($digital as $att) {
            $digitalMap[$att->student_id][$att->date->toDateString()] = $att;
            $institutionIds[$att->institution_id] = true;
        }

        $leaveMap = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        foreach ($requests as $request) {
            $from = Carbon::parse($request->date->toDateString())->max($start);
            $to = Carbon::parse($request->effectiveEndDate()->toDateString())->min($end);
            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $leaveMap[$request->student_id][$d->toDateString()] = $request;
            }
        }

        $students = User::whereIn('id', $studentIds)->get()->keyBy('id');
        foreach ($students as $student) {
            if ($student->institution_id) {
                $institutionIds[$student->institution_id] = true;
            }
        }

        $settings = [];
        foreach (array_keys($institutionIds) as $institutionId) {
            $settings[$institutionId] = DailyAttendanceSetting::forInstitution($institutionId);
        }

        $allDates = collect();
        foreach ($studentIds as $studentId) {
            $allDates = $allDates
                ->merge(array_keys($manualMap[$studentId] ?? []))
                ->merge(array_keys($digitalMap[$studentId] ?? []))
                ->merge(array_keys($leaveMap[$studentId] ?? []));
        }
        $allDates = $allDates->unique()->sort();

        $result = [];
        foreach ($studentIds as $studentId) {
            $resolved = [];
            foreach ($allDates as $date) {
                if (isset($leaveMap[$studentId][$date]) && $leaveMap[$studentId][$date]->isTidakHadirCategory()) {
                    $resolved[$date] = $this->resolver->deriveStatus(null, $leaveMap[$studentId][$date], false, $date);

                    continue;
                }

                if (isset($manualMap[$studentId][$date])) {
                    $resolved[$date] = $manualMap[$studentId][$date];

                    continue;
                }

                if (isset($leaveMap[$studentId][$date])) {
                    $resolved[$date] = $this->resolver->deriveStatus(null, $leaveMap[$studentId][$date], false, $date);

                    continue;
                }

                $attendance = $digitalMap[$studentId][$date] ?? null;
                if ($attendance) {
                    $setting = $settings[$attendance->institution_id] ?? null;
                    $deadline = Carbon::parse($date.' '.($setting->check_in_verification_deadline ?? '23:59'));
                    $resolved[$date] = $this->resolver->deriveStatus($attendance, null, now()->greaterThan($deadline), $date);

                    continue;
                }

                $student = $students->get($studentId);
                if ($student && $student->institution_id) {
                    $setting = $settings[$student->institution_id] ?? null;
                    $deadline = Carbon::parse($date.' '.($setting->check_in_verification_deadline ?? '23:59:00'));
                    $resolved[$date] = $this->resolver->deriveStatus(null, null, now()->greaterThan($deadline), $date);
                }
            }

            $result[$studentId] = $resolved;
        }

        return $result;
    }

    /**
     * Resolve a single status per student for one date.
     *
     * Final presensi wins over digital kehadiran and approved izin/dispen.
     * 'not_yet' means the student has no data and the verification deadline
     * has not passed yet.
     *
     * @param  array<int, int>  $studentIds
     * @param  array<int, int>|null  $academicYearIds
     * @return array<int, string|null> student_id => status|null
     */
    public function resolveForDate(array $studentIds, string $date, ?array $academicYearIds = null): array
    {
        $resolved = $this->resolveForRange($studentIds, $date, $date, $academicYearIds);

        $result = [];
        $missing = [];
        foreach ($studentIds as $studentId) {
            $status = $resolved[$studentId][$date] ?? null;
            $result[$studentId] = $status;
            if ($status === null) {
                $missing[] = $studentId;
            }
        }

        if ($missing) {
            $this->resolveWithoutData($missing, $date, $result);
        }

        return $result;
    }

    /**
     * Resolve status for students without any manual/digital/izin record on the date.
     *
     * Mirrors the digital kehadiran monitor: no check-in + verification deadline
     * passed = 'absent' (alpha), otherwise 'not_yet'.
     *
     * @param  array<int, int>  $studentIds
     * @param  array<int, string|null>  $result
     */
    private function resolveWithoutData(array $studentIds, string $date, array &$result): void
    {
        $students = User::whereIn('id', $studentIds)->get()->keyBy('id');

        $leaveRequests = StudentEarlyLeaveRequest::whereIn('student_id', $studentIds)
            ->where('status', 'approved')
            ->whereDate('date', '<=', $date)
            ->whereRaw('COALESCE(date_end, date) >= ?', [$date])
            ->get()
            ->groupBy('student_id');

        $digital = StudentDailyAttendance::whereIn('student_id', $studentIds)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        foreach ($studentIds as $studentId) {
            $student = $students->get($studentId);
            if (! $student) {
                continue;
            }

            $setting = DailyAttendanceSetting::forInstitution($student->institution_id);
            $deadline = Carbon::parse($date.' '.($setting->check_in_verification_deadline ?? '23:59:00'));

            $result[$studentId] = $this->resolver->deriveStatus(
                $digital->get($studentId),
                $leaveRequests->get($studentId)?->first(),
                now()->greaterThan($deadline),
                $date
            );
        }
    }

    /**
     * Aggregate resolved statuses into counts per status key.
     *
     * @param  array<int, array<string, string>>  $resolved
     * @return array{total:int,present:int,absent:int,late:int,excused:int,sick:int,not_yet:int,unknown:int}
     */
    public function countStatuses(array $resolved): array
    {
        $counts = ['total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'sick' => 0, 'not_yet' => 0, 'unknown' => 0];

        foreach ($resolved as $studentStatuses) {
            foreach ($studentStatuses as $status) {
                $counts['total']++;
                if (isset($counts[$status])) {
                    $counts[$status]++;
                }
            }
        }

        return $counts;
    }

    /**
     * Aggregate resolved statuses into per-student counts plus derived fields.
     *
     * @param  array<int, array<string, string>>  $resolved
     * @return array<int, array{
     *     total:int, present:int, absent:int, late:int, excused:int, sick:int,
     *     not_yet:int, unknown:int, main_status:?string, rate:float
     * }>
     */
    public function countPerStudent(array $resolved): array
    {
        $result = [];
        foreach ($resolved as $studentId => $statuses) {
            $counts = ['total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'sick' => 0, 'not_yet' => 0, 'unknown' => 0];
            $mainStatus = null;
            $latestDate = null;

            foreach ($statuses as $date => $status) {
                $counts['total']++;
                if (isset($counts[$status])) {
                    $counts[$status]++;
                }
                if ($latestDate === null || $date > $latestDate) {
                    $latestDate = $date;
                    $mainStatus = $status;
                }
            }

            $counts['main_status'] = $mainStatus;
            $counts['rate'] = $counts['total'] > 0
                ? round((($counts['present'] + $counts['late']) / $counts['total']) * 100, 1)
                : 0;

            $result[$studentId] = $counts;
        }

        return $result;
    }
}
