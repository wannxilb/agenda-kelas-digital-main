<?php

namespace App\Console\Commands;

use App\Models\DailyAttendanceSetting;
use App\Models\Setting;
use App\Models\StudentDailyAttendance;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\DailyAttendancePresensiSync;
use App\Support\DailyAttendanceWhatsappNotifier;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCheckoutStudents extends Command
{
    protected $signature = 'attendance:auto-checkout {--date= : Specific date to process (Y-m-d)}';
    protected $description = 'Automatically check out students who have not checked out by the scheduled time';

    public function handle(): int
    {
        $today = $this->option('date') ?? Carbon::today()->toDateString();
        $processed = 0;

        $institutionIds = DailyAttendanceSetting::pluck('institution_id')->unique();

        foreach ($institutionIds as $institutionId) {
            $setting = DailyAttendanceSetting::forInstitution($institutionId);
            $effectiveCheckoutTime = $this->resolveCheckoutTime($setting, $today);

            $checkoutMoment = Carbon::parse($today . ' ' . $effectiveCheckoutTime);

            if (Carbon::now()->lt($checkoutMoment)) {
                continue;
            }

            $attendances = StudentDailyAttendance::withoutGlobalScopes()
                ->whereDate('date', $today)
                ->whereNull('check_out_at')
                ->whereNotNull('check_in_at')
                ->where('institution_id', $institutionId)
                ->get();

            foreach ($attendances as $attendance) {
                if ($attendance->check_out_at) {
                    continue;
                }

                $attendance->update([
                    'check_out_at' => $checkoutMoment,
                    'check_out_status' => 'auto_checkout',
                    'check_out_verification_method' => 'auto_checkout',
                ]);

                app(DailyAttendancePresensiSync::class)->sync($attendance->refresh());

                $student = $attendance->student()->first();
                if ($student) {
                    $settingForStudent = DailyAttendanceSetting::forInstitution($student->institution_id);
                    app(DailyAttendanceWhatsappNotifier::class)->sendIfEligible(
                        $student,
                        $attendance->refresh(),
                        $settingForStudent,
                        'check_out'
                    );

                    AuditLogger::log(
                        'auto_checkout',
                        'student_daily_attendances',
                        $attendance->id,
                        $attendance->institution_id,
                        null,
                        [
                            'student_id' => $student->id,
                            'check_out_at' => $checkoutMoment->toIso8601String(),
                        ]
                    );
                }

                $processed++;
            }
        }

        $this->info("Auto-checkout selesai. {$processed} siswa di-checkout otomatis.");
        return self::SUCCESS;
    }

    private function resolveCheckoutTime(DailyAttendanceSetting $setting, string $date): string
    {
        if ($setting->check_out_override_date && $setting->check_out_override_time) {
            if (Carbon::parse($setting->check_out_override_date)->toDateString() === $date) {
                return $setting->check_out_override_time;
            }
        }

        return Setting::get('operational_end_time', '16:00', $setting->institution_id);
    }
}
