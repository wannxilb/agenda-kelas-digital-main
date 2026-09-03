<?php

namespace App\Http\Controllers\Wakasek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Agenda;
use App\Models\Subject;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class TeachingController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('view', User::class);

        $query = User::role('teacher')
            ->withCount(['agendas' => function ($q) {
                $q->where('status', 'published');
            }])
            ->with(['subjects', 'teachingSchedules']);

        if (request('q')) {
            $query->where(function ($q) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower(request('q')) . '%'])
                    ->orWhereRaw('LOWER(nip) LIKE ?', ['%' . mb_strtolower(request('q')) . '%']);
            });
        }

        if (request('subject')) {
            $query->whereHas('subjects', function ($q) {
                $q->where('subject_id', request('subject'));
            });
        }

        if (request('status') === 'active') {
            $query->has('agendas', '>', 0);
        } elseif (request('status') === 'inactive') {
            $query->has('agendas', '<', 1);
        }

        $teachers = $query->orderBy('name', 'asc')->get();

        $totalJournals = $teachers->sum('agendas_count');
        $activeTeachers = $teachers->filter(fn($t) => $t->agendas_count > 0)->count();
        $inactiveTeachers = $teachers->filter(fn($t) => $t->agendas_count === 0)->count();
        $avgPerTeacher = $activeTeachers > 0 ? round($totalJournals / $activeTeachers, 1) : 0;

        $subjects = Subject::orderBy('name')->get();

        return view('wakasek.teaching.index', compact(
            'teachers',
            'totalJournals',
            'activeTeachers',
            'inactiveTeachers',
            'avgPerTeacher',
            'subjects'
        ));
    }

    public function show(User $teacher)
    {
        $this->authorize('view', User::class);

        $teacher->load(['subjects', 'teachingSchedules.class', 'teachingSchedules.subject']);

        $query = Agenda::where('teacher_id', $teacher->id)
            ->where('status', 'published')
            ->with(['class', 'subject']);

        if (request('q')) {
            $query->where(function ($q) {
                $q->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower(request('q')) . '%'])
                    ->orWhereRaw('LOWER(description) LIKE ?', ['%' . mb_strtolower(request('q')) . '%'])
                    ->orWhereHas('class', function ($c) {
                        $c->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower(request('q')) . '%']);
                    })
                    ->orWhereHas('subject', function ($s) {
                        $s->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower(request('q')) . '%']);
                    });
            });
        }

        $agendas = $query->orderBy('date', 'desc')->paginate(15);

        $totalAgendas = Agenda::where('teacher_id', $teacher->id)->where('status', 'published')->count();
        $totalSchedules = $teacher->teachingSchedules()->count();
        $uniqueClasses = Agenda::where('teacher_id', $teacher->id)
            ->where('status', 'published')
            ->distinct('class_id')
            ->count('class_id');

        $driver = DB::connection()->getDriverName();
        $monthSelect = $driver === 'sqlite' ? "strftime('%Y-%m', date)" : ($driver === 'pgsql' ? "TO_CHAR(date, 'YYYY-MM')" : "DATE_FORMAT(date, '%Y-%m')");

        $monthlyStats = Agenda::where('teacher_id', $teacher->id)
            ->where('status', 'published')
            ->selectRaw("$monthSelect as month, COUNT(*) as total")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->pluck('total', 'month');

        $todayCount = Agenda::where('teacher_id', $teacher->id)
            ->where('status', 'published')
            ->whereDate('date', today())
            ->count();

        $weekCount = Agenda::where('teacher_id', $teacher->id)
            ->where('status', 'published')
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $subjects = $teacher->subjects;

        return view('wakasek.teaching.show', compact(
            'teacher',
            'agendas',
            'totalAgendas',
            'totalSchedules',
            'subjects',
            'uniqueClasses',
            'monthlyStats',
            'todayCount',
            'weekCount'
        ));
    }
}
