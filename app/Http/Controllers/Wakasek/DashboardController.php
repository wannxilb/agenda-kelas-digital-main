<?php

namespace App\Http\Controllers\Wakasek;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\TeacherStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DashboardController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('view', User::class);

        $total_teachers = User::role('teacher')->where('status', 'active')->count();
        $total_classes = Classes::count();
        $total_subjects = Subject::count();

        $today = Carbon::today()->toDateString();
        $dayName = Carbon::parse($today)->format('l');
        $now = Carbon::now();

        $agendas_today = Agenda::where('status', 'published')->whereDate('date', $today)->count();

        $latest_agendas = Agenda::where('status', 'published')
            ->with(['teacher', 'subject', 'class'])
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        // Statistik per kelas
        $class_stats = Classes::withCount(['agendas' => function ($q) {
            $q->where('status', 'published');
        }])->get();

        // Hanya status APPROVED + rentang tanggal mencakup hari ini (multi-hari).
        // pending/rejected/cancelled tidak menghitung slot "terisi".
        $teacherStatuses = TeacherStatus::query()
            ->where('status', 'approved')
            ->where('date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('date_end')->orWhere('date_end', '>=', $today);
            })
            ->get()
            ->keyBy('teacher_id');

        $monitoring_classes = Classes::with([
            'schedules' => function ($q) use ($dayName) {
                $q->where('day', $dayName)->with(['subject', 'teacher'])->orderBy('start_time');
            },
            'agendas' => function ($q) use ($today) {
                $q->whereDate('date', $today)
                    ->where('status', 'published')
                    ->with(['subject', 'teacher']);
            },
        ])->orderBy('grade_level')->orderBy('name')->get()->map(function ($class) use ($today, $now, $teacherStatuses) {
            $pastFilled = 0;
            $pastEmpty = 0;
            $filledSlots = 0;
            $emptyUpcomingSlots = 0;

            foreach ($class->schedules as $schedule) {
                $hasAgenda = $class->agendas->contains(function ($agenda) use ($schedule) {
                    if (! empty($agenda->schedule_id)) {
                        return (int) $agenda->schedule_id === (int) $schedule->id;
                    }

                    return (int) $agenda->subject_id === (int) $schedule->subject_id
                        && (int) $agenda->teacher_id === (int) $schedule->teacher_id;
                });
                $hasStatus = isset($teacherStatuses[$schedule->teacher_id]);
                $isFilled = $hasAgenda || $hasStatus;
                $slotEnd = $schedule->end_time ? Carbon::parse($today.' '.$schedule->end_time) : null;
                $isPast = $slotEnd && $slotEnd->lt($now);

                if ($isFilled) {
                    $filledSlots++;
                }

                if ($isPast) {
                    $isFilled ? $pastFilled++ : $pastEmpty++;
                } elseif (! $isFilled) {
                    $emptyUpcomingSlots++;
                }
            }

            $totalPast = $pastFilled + $pastEmpty;
            if ($class->schedules->isEmpty()) {
                $rowStatus = 'kosong';
            } elseif ($totalPast === 0 || $pastEmpty === 0) {
                $rowStatus = 'lengkap';
            } elseif ($pastFilled === 0) {
                $rowStatus = 'jamkos';
            } else {
                $rowStatus = 'belum_terisi';
            }

            $class->monitoring_status = $rowStatus;
            $class->monitoring_filled_slots = $filledSlots;
            $class->monitoring_total_slots = $class->schedules->count();
            $class->monitoring_empty_upcoming_slots = $emptyUpcomingSlots;

            return $class;
        })->sortBy(function ($class) {
            return [
                'jamkos' => 0,
                'belum_terisi' => 1,
                'lengkap' => 2,
                'kosong' => 3,
            ][$class->monitoring_status] ?? 4;
        })->values();

        // Jumlah pengajuan izin/tugas luar yang menunggu persetujuan
        $pendingTeacherStatuses = TeacherStatus::pending()->count();

        return view('wakasek.dashboard', [
            'total_teachers' => $total_teachers,
            'total_classes' => $total_classes,
            'total_subjects' => $total_subjects,
            'agendas_today' => $agendas_today,
            'latest_agendas' => $latest_agendas,
            'class_stats' => $class_stats,
            'monitoring_classes' => $monitoring_classes,
            'pendingTeacherStatuses' => $pendingTeacherStatuses,
        ]);
    }
}
