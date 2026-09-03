<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Agenda;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /** Masa berlaku cache data dashboard (detik). Sinkron dengan polling 30s di client. */
    private const DASHBOARD_CACHE_TTL = 120;

    public function index()
    {
        $data = $this->getDashboardData();
        return view('admin.dashboard', $data);
    }

    /**
     * API for real-time dashboard updates
     */
    public function statsApi()
    {
        return response()->json($this->getDashboardData());
    }

    private function getDashboardData()
    {
        $yearSuffix = session('academic_year_id') ?? 'active';
        $cacheKey = 'admin.dashboard.v1:' . (auth()->user()?->institution_id ?? 'global') . ':' . $yearSuffix;

        return Cache::remember($cacheKey, self::DASHBOARD_CACHE_TTL, function () {
        // Statistik utama
        $stats = [
            'total_classes' => Classes::count(),
            'total_students' => User::role('siswa')->where('status', 'active')->count(),
            'total_teachers' => User::role('teacher')->count(),
            'total_subjects' => Subject::count(),
            'total_agendas_today' => Agenda::query()->where('status', 'published')->where('date', date('Y-m-d'))->count(),
            'attendance_rate' => $this->getAttendanceRate(),
        ];

        // Aktivitas terbaru
        $recent_activities = Agenda::where('status', 'published')
            ->with(['class', 'teacher'])
            ->latest()
            ->take(5)
            ->get();

        // Distribusi kelas
        $class_distribution = Classes::withCount('students')
            ->orderBy('name')->get()
            ->map(function($class) {
                return [
                    'name' => $class->name,
                    'count' => $class->students_count,
                    'capacity' => $class->capacity ?? 36
                ];
            });

        // Presensi hari ini
        $today_attendance = Attendance::query()->where('date', date('Y-m-d'))
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $driver = DB::connection()->getDriverName();
        $monthSelect = $driver === 'sqlite' ? 'strftime("%Y-%m", date)' : ($driver === 'pgsql' ? "to_char(date, 'YYYY-MM')" : "DATE_FORMAT(date, '%Y-%m')");

        // Agenda per bulan
        $monthly_agendas = Agenda::where('status', 'published')
            ->select(
                DB::raw($monthSelect . ' as month'),
                DB::raw('count(*) as total')
            )
            ->whereYear('date', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Status Ruangan (Dinamis dari tabel rooms)
        $rooms = \App\Models\Room::where('is_active', true)->get();
        $room_status = $rooms->mapWithKeys(function ($room) {
            $now = now();
            // Ambil jadwal aktif jika ada
            $activeSchedule = \App\Models\Schedule::currentlyOccupied($now)
                ->where(function($q) use ($room) {
                    $q->where('room_id', $room->id)
                      ->orWhere('room', $room->name);
                })
                ->with(['class', 'teacher'])
                ->first();

            return [$room->name => [
                'status' => $activeSchedule ? 'occupied' : 'available',
                'details' => $activeSchedule ? [
                    'class' => $activeSchedule->class->name ?? '-',
                    'teacher' => $activeSchedule->teacher->name ?? '-',
                    'end_time' => \Carbon\Carbon::parse($activeSchedule->end_time)->format('H:i')
                ] : null
            ]];
        });

        return [
            'stats' => $stats, 
            'recent_activities' => $recent_activities, 
            'class_distribution' => $class_distribution,
            'today_attendance' => $today_attendance,
            'monthly_agendas' => $monthly_agendas,
            'room_status' => $room_status
        ];
        });
    }

    private function getAttendanceRate()
    {
        $total = Attendance::whereMonth('date', date('m'))->count();
        if ($total == 0) return 0;
        
        $present = Attendance::where('status', 'present')
            ->whereMonth('date', date('m'))
            ->count();
            
        return round(($present / $total) * 100, 2);
    }

    /** Masa berlaku cache data monitoring (detik). */
    private const MONITORING_CACHE_TTL = 60;

    public function monitoringClasses()
    {
        $classes = $this->getMonitoringClasses();
        $classesJson = json_encode($classes);
        return view('admin.monitoring.classes', compact('classes', 'classesJson'));
    }

    public function monitoringClassesApi()
    {
        return response()->json($this->getMonitoringClasses());
    }

    public function monitoringTeachers(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $teachers = $this->getMonitoringTeachers($search);
        $teachersJson = json_encode($teachers);
        return view('admin.monitoring.teachers', compact('teachers', 'teachersJson'));
    }

    public function monitoringTeachersApi(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        return response()->json($this->getMonitoringTeachers($search));
    }

    private function getMonitoringClasses(): array
    {
        $institutionId = auth()->user()?->institution_id ?? 'global';
        $cacheKey = 'admin.monitoring.classes.v1:' . $institutionId;

        return Cache::remember($cacheKey, self::MONITORING_CACHE_TTL, function () {
            return Classes::with(['homeroomTeacher'])
                ->withCount(['students', 'agendas' => function ($q) {
                    $q->where('status', 'published');
                }])
                ->orderBy('name')->get()
                ->map(function ($class) {
                    return [
                        'id' => $class->id,
                        'name' => $class->name,
                        'grade_level' => $class->grade_level,
                        'capacity' => $class->capacity ?? 36,
                        'homeroom' => $class->homeroomTeacher->name ?? '-',
                        'students_count' => $class->students_count,
                        'agendas_count' => $class->agendas_count,
                        'attendance_rate' => $this->getClassAttendanceRate($class->id),
                    ];
                })
                ->values()
                ->all();
        });
    }

    private function getMonitoringTeachers(string $search = ''): array
    {
        $institutionId = auth()->user()?->institution_id ?? 'global';
        $cacheKey = 'admin.monitoring.teachers.v1:' . $institutionId . ':' . md5($search);

        return Cache::remember($cacheKey, self::MONITORING_CACHE_TTL, function () use ($search) {
            $query = User::role('teacher')
                ->with(['subjects'])
                ->withCount(['agendas' => function ($q) {
                    $q->where('status', 'published');
                }])
                ->withCount(['agendas as monthly_agendas_count' => function($q) {
                    $q->where('status', 'published')->whereMonth('date', now()->month)
                      ->whereYear('date', now()->year);
                }]);

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('nip', 'ilike', "%{$search}%");
                });
            }

            return $query->orderBy('name')->get()
                ->map(function ($teacher) {
                    return [
                        'id' => $teacher->id,
                        'name' => $teacher->name,
                        'initial' => strtoupper(substr($teacher->name, 0, 1)),
                        'nip' => $teacher->nip ?? '-',
                        'subjects' => $teacher->subjects->take(3)->pluck('name')->all(),
                        'agendas_count' => $teacher->agendas_count ?? 0,
                        'monthly_agendas_count' => $teacher->monthly_agendas_count ?? 0,
                    ];
                })
                ->values()
                ->all();
        });
    }

    private function getClassAttendanceRate(int|string $classId): float|int
    {
        $total = Attendance::whereHas('student', function($q) use ($classId) {
                $q->where('class_id', $classId);
            })
            ->whereMonth('date', date('m'))
            ->count();
            
        if ($total == 0) return 0;
        
        $present = Attendance::where('status', 'present')
            ->whereHas('student', function($q) use ($classId) {
                $q->where('class_id', $classId);
            })
            ->whereMonth('date', date('m'))
            ->count();
            
        return round(($present / $total) * 100, 2);
    }
}
