<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Institution;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\StudentDailyAttendance;
use App\Models\Subject;
use App\Services\AuditLogger;
use App\Services\FeatureService;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    public function index()
    {
        $institutions = Institution::withCount(['users as students_count' => function ($q) {
            $q->whereHas('roles', fn($r) => $r->where('name', 'siswa'));
        }, 'users as teachers_count' => function ($q) {
            $q->whereHas('roles', fn($r) => $r->where('name', 'teacher'));
        }, 'users as admins_count' => function ($q) {
            $q->whereHas('roles', fn($r) => $r->where('name', 'admin'));
        }])->get();

        return view('super_admin.institutions.index', compact('institutions'));
    }

    public function create()
    {
        return view('super_admin.institutions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:institutions,email',
            'status' => 'nullable|in:active,suspended,maintenance,expired',
        ], $this->validationMessages());

        $institution = Institution::create($request->only(['name', 'logo', 'favicon', 'address', 'phone', 'email', 'status']));

        AuditLogger::log('Menambah Institusi', 'institutions', $institution->id, $institution->id, null, $institution->toArray());

        return redirect()->route('super-admin.institutions.index')->with('success', __('Instansi berhasil ditambahkan.'));
    }

    protected function validationMessages()
    {
        $email = request('email');

        if (!$email) {
            return [];
        }

        $existing = Institution::where('email', $email)->first(['name']);

        if ($existing) {
            return [
                'email.unique' => __('Email ini sudah digunakan oleh ":instansi". Silakan gunakan email yang berbeda.', ['instansi' => $existing->name]),
            ];
        }

        return [
            'email.unique' => __('Email ini sudah digunakan oleh instansi lain. Silakan gunakan email yang berbeda.'),
        ];
    }

    public function edit(Institution $institution)
    {
        return view('super_admin.institutions.edit', compact('institution'));
    }

    public function show(Institution $institution)
    {
        $today = now()->toDateString();

        $stats = [
            'students' => \App\Models\User::role('siswa')
                            ->where('status', 'active')
                            ->where('institution_id', $institution->id)
                            ->count(),
            'inactive_students' => \App\Models\User::role('siswa')
                            ->where('status', 'inactive')
                            ->where('institution_id', $institution->id)
                            ->count(),
            'graduated_students' => \App\Models\User::role('siswa')
                            ->where('status', 'graduated')
                            ->where('institution_id', $institution->id)
                            ->count(),
            'teachers' => $institution->users()->role('teacher')->count(),
            'admins' => $institution->users()->role('admin')->count(),
            'classes' => Classes::where('institution_id', $institution->id)->count(),
            'subjects' => Subject::where('institution_id', $institution->id)->count(),
            'schedules' => Schedule::where('institution_id', $institution->id)->count(),
            'rooms' => \App\Models\Room::where('institution_id', $institution->id)->count(),
            'agendas_today' => Agenda::where('institution_id', $institution->id)
                ->whereDate('date', $today)
                ->count(),
            'attendances_today' => Attendance::where('institution_id', $institution->id)
                ->whereDate('date', $today)
                ->count(),
            'daily_attendances_today' => StudentDailyAttendance::where('institution_id', $institution->id)
                ->whereDate('date', $today)
                ->count(),
        ];

        $features = collect(FeatureService::all())->map(function ($feature, $name) use ($institution) {
            $institutionSetting = Setting::where('key', $feature['key'])
                ->where('institution_id', $institution->id)
                ->first();

            return $feature + [
                'name' => $name,
                'enabled' => FeatureService::isEnabled($name, $institution->id),
                'uses_global' => $institutionSetting === null,
                'override_value' => $institutionSetting?->value,
                'global_enabled' => FeatureService::isEnabled($name, null),
            ];
        });

        $recentLogs = \App\Models\AuditLog::with('user')
            ->where('institution_id', $institution->id)
            ->latest()
            ->limit(8)
            ->get();

        return view('super_admin.institutions.show', compact('institution', 'stats', 'features', 'recentLogs'));
    }

    public function updateFeatures(Request $request, Institution $institution)
    {
        $validated = $request->validate([
            'features' => ['array'],
            'features.*' => ['nullable', 'in:inherit,1,0'],
        ]);

        $requestedFeatures = $validated['features'] ?? [];
        $oldValues = Setting::where('institution_id', $institution->id)
            ->whereIn('key', array_column(FeatureService::all(), 'key'))
            ->pluck('value', 'key')
            ->all();

        foreach (FeatureService::all() as $feature) {
            $value = $requestedFeatures[$feature['key']] ?? 'inherit';

            if ($value === 'inherit') {
                Setting::where('institution_id', $institution->id)
                    ->where('key', $feature['key'])
                    ->delete();
                continue;
            }

            Setting::set($feature['key'], $value, 'features', $institution->id);
        }

        $newValues = Setting::where('institution_id', $institution->id)
            ->whereIn('key', array_column(FeatureService::all(), 'key'))
            ->pluck('value', 'key')
            ->all();

        AuditLogger::log('Mengubah Fitur Instansi', 'settings', $institution->id, $institution->id, $oldValues, $newValues);

        return redirect()
            ->route('super-admin.institutions.show', $institution)
            ->with('success', __('Pengaturan fitur instansi berhasil diperbarui.'));
    }

    public function update(Request $request, Institution $institution)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:institutions,email,' . $institution->id,
            'status' => 'nullable|in:active,suspended,maintenance,expired',
        ], $this->validationMessages());

        $oldValues = $institution->toArray();
        $institution->update($request->only(['name', 'logo', 'favicon', 'address', 'phone', 'email', 'status']));

        AuditLogger::log('Mengubah Institusi', 'institutions', $institution->id, $institution->id, $oldValues, $institution->toArray());

        return redirect()->route('super-admin.institutions.index')->with('success', __('Instansi berhasil diperbarui.'));
    }

    public function destroy(Institution $institution)
    {
        AuditLogger::log('Menghapus Institusi', 'institutions', $institution->id, $institution->id, $institution->toArray(), null);
        
        $institution->delete();
        return redirect()->route('super-admin.institutions.index')->with('success', __('Instansi berhasil dihapus.'));
    }

    public function trash()
    {
        $institutions = Institution::onlyTrashed()->get();
        return view('super_admin.institutions.trash', compact('institutions'));
    }

    public function restore(int|string $id)
    {
        $institution = Institution::onlyTrashed()->findOrFail($id);
        $institution->restore();

        AuditLogger::log('Memulihkan Institusi', 'institutions', $institution->id, $institution->id, null, $institution->toArray());

        return redirect()->route('super-admin.institutions.trash')->with('success', __('Instansi berhasil dipulihkan.'));
    }

    public function forceDelete(int|string $id)
    {
        $institution = Institution::onlyTrashed()->findOrFail($id);
        
        AuditLogger::log('Menghapus Permanen Institusi', 'institutions', $institution->id, $institution->id, $institution->toArray(), null);
        
        $institution->forceDelete();

        return redirect()->route('super-admin.institutions.trash')->with('success', __('Instansi berhasil dihapus permanen.'));
    }
}
