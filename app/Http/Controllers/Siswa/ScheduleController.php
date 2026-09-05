<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\TeacherStatus;
use App\Services\TeacherStatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function __construct(private TeacherStatusService $teacherStatusService) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $classId = $user->class_id;

        if (! $classId) {
            return view('siswa.schedule.index', [
                'days' => [],
                'schedules' => [],
                'recurringByDay' => [],
                'weekRange' => '',
                'weekTypeLabel' => null,
                'currentDate' => Carbon::today()->format('Y-m-d'),
                'todayIndex' => 0,
                'noClass' => true,
            ]);
        }

        $activeYearId = session('academic_year_id') ?: AcademicYear::where('is_active', true)->first()?->id;

        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $delta = (int) $request->input('delta', 0);

        $currentDate = Carbon::parse($date)->addWeeks($delta);
        $startOfWeek = $currentDate->copy()->startOfWeek();
        $endOfWeek = $currentDate->copy()->endOfWeek();

        $weekRange = $startOfWeek->translatedFormat('d M').' - '.$endOfWeek->translatedFormat('d M Y');

        $days = [];
        for ($i = 0; $i < 5; $i++) {
            $dayDate = $startOfWeek->copy()->addDays($i);
            $days[$i] = [
                'name' => $dayDate->translatedFormat('l'),
                'day_number' => $dayDate->format('d'),
                'month_name' => $dayDate->translatedFormat('M'),
                'date' => $dayDate->format('d/m'),
                'is_today' => $dayDate->isToday(),
                'date_full' => $dayDate->format('Y-m-d'),
            ];
        }

        $dayMapping = [
            0 => 'Monday',
            1 => 'Tuesday',
            2 => 'Wednesday',
            3 => 'Thursday',
            4 => 'Friday',
        ];

        $weekTypes = \App\Models\Setting::scheduleWeekTypesForDate($currentDate);

        $allSchedules = Schedule::where('class_id', $classId)
            ->where('academic_year_id', $activeYearId)
            ->whereIn('day', array_values($dayMapping))
            ->whereIn('week_type', $weekTypes)
            ->with(['subject', 'teacher'])
            ->orderBy('start_time')
            ->get()
            ->groupBy('day');

        $teacherIds = $allSchedules
            ->flatten()
            ->pluck('teacher_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $teacherStatuses = $this->teacherStatusService->approvedForDateRange(
            $startOfWeek,
            $endOfWeek,
            $teacherIds
        );

        $schedules = [];
        foreach ($dayMapping as $index => $englishDay) {
            $schedules[$index] = $allSchedules->get($englishDay, collect());
            $this->annotateSchedulesForDate($schedules[$index], $teacherStatuses, $days[$index]['date_full']);
        }

        $recurringByDay = $this->recurringByIndex($days, $dayMapping);

        $todayIndex = 0;
        foreach ($days as $i => $day) {
            if ($day['is_today']) {
                $todayIndex = $i;
                break;
            }
        }

        return view('siswa.schedule.index', [
            'days' => $days,
            'schedules' => $schedules,
            'recurringByDay' => $recurringByDay,
            'weekRange' => $weekRange,
            'weekTypeLabel' => \App\Models\Setting::scheduleMode() === 'block' ? (\App\Models\Setting::scheduleWeekTypesForDate($currentDate)[0] ?? null) : null,
            'currentDate' => $currentDate->format('Y-m-d'),
            'todayIndex' => $todayIndex,
            'noClass' => false,
        ]);
    }

    /**
     * Memetakan kegiatan rutin (setting recurring_activities) ke index hari
     * supaya mudah dirender di view jadwal harian siswa.
     */
    private function recurringByIndex(array $days, array $dayMapping): array
    {
        $activities = \App\Models\Setting::recurringActivities();

        $result = [];
        foreach ($dayMapping as $index => $englishDay) {
            $result[$index] = array_values(array_filter($activities, function ($act) use ($englishDay) {
                return in_array($englishDay, $act['days'] ?? [], true);
            }));
        }

        return $result;
    }

    public function byDate(Request $request)
    {
        $user = Auth::user();
        $classId = $user->class_id;

        if (! $classId) {
            return response()->json([]);
        }

        $request->validate([
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($request->date);
        $dayName = $date->locale('en')->format('l');

        $activeYearId = session('academic_year_id') ?: AcademicYear::where('is_active', true)->first()?->id;

        $schedules = Schedule::where('class_id', $classId)
            ->where('academic_year_id', $activeYearId)
            ->where('day', $dayName)
            ->whereIn('week_type', \App\Models\Setting::scheduleWeekTypesForDate($date))
            ->with(['subject', 'teacher'])
            ->orderBy('start_time')
            ->get();

        $teacherStatuses = $this->teacherStatusService->approvedForDateRange(
            $date,
            $date,
            $schedules->pluck('teacher_id')->filter()->unique()->values()->all()
        );
        $this->annotateSchedulesForDate($schedules, $teacherStatuses, $date->toDateString());

        $schedules = $schedules->map(function ($s) {
            $teacherStatus = $s->teacherStatusForStudent;

            return [
                'subject_name' => $s->subject->name,
                'teacher_name' => $s->teacher->name,
                'start_time' => Carbon::parse($s->start_time)->format('H:i'),
                'end_time' => Carbon::parse($s->end_time)->format('H:i'),
                'room' => $s->room,
                'teacher_status' => $teacherStatus ? [
                    'type' => $teacherStatus->type,
                    'label' => $teacherStatus->typeLabel(),
                    'substitute_teacher_name' => $teacherStatus->substituteTeacher?->name,
                ] : null,
            ];
        });

        return response()->json($schedules);
    }

    public function changeWeek(Request $request)
    {
        $request->validate([
            'current_date' => 'required|date',
            'delta' => 'required|integer|between:-52,52',
        ]);

        $currentDate = Carbon::parse($request->current_date)->addWeeks((int) $request->delta);
        $startOfWeek = $currentDate->copy()->startOfWeek();
        $endOfWeek = $currentDate->copy()->endOfWeek();

        return response()->json([
            'current_date' => $currentDate->format('Y-m-d'),
            'week_range' => $startOfWeek->translatedFormat('d M').' - '.$endOfWeek->translatedFormat('d M Y'),
        ]);
    }

    public function todayDate()
    {
        $date = Carbon::today();
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'week_range' => $startOfWeek->translatedFormat('d M').' - '.$endOfWeek->translatedFormat('d M Y'),
        ]);
    }

    private function annotateSchedulesForDate(Collection $schedules, Collection $teacherStatuses, string $date): void
    {
        $statusesByTeacher = $teacherStatuses->groupBy('teacher_id');

        foreach ($schedules as $schedule) {
            $status = $statusesByTeacher
                ->get($schedule->teacher_id, collect())
                ->first(fn (TeacherStatus $teacherStatus) => $teacherStatus->coversDate($date));

            $schedule->setRelation('teacherStatusForStudent', $status);
        }
    }
}
