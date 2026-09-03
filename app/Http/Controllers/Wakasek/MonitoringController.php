<?php

namespace App\Http\Controllers\Wakasek;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\TeacherStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('view', User::class);

        $date = $request->query('date', date('Y-m-d'));
        $dayName = Carbon::parse($date)->format('l');

        // Hanya status APPROVED + rentang tanggal mencakup $date (multi-hari).
        // pending/rejected/cancelled tidak menghitung slot "terisi".
        $teacherStatuses = TeacherStatus::query()
            ->where('status', 'approved')
            ->where('date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('date_end')->orWhere('date_end', '>=', $date);
            })
            ->get()
            ->keyBy('teacher_id');

        $classes = Classes::with([
            'schedules' => function ($q) use ($dayName) {
                $q->where('day', $dayName)->with(['subject', 'teacher'])->orderBy('start_time');
            },
            'agendas' => function ($q) use ($date) {
                $q->whereDate('date', $date)
                    ->where('status', 'published')
                    ->with(['subject', 'teacher']);
            },
        ])->orderBy('grade_level')->orderBy('name')->get();

        return view('wakasek.monitoring.agenda', compact('classes', 'date', 'teacherStatuses'));
    }
}
