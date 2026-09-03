<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Institution;
use App\Models\Setting;
use App\Models\TeacherStatus;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        $institutions = Institution::with(['users' => function($q) {
            $q->role('admin');
        }])->get();
        return view('super_admin.admins.index', compact('institutions'));
    }

    public function create()
    {
        $institutions = Institution::withCount(['users' => function($q) {
            $q->role('admin');
        }])->get()->filter(function($institution) {
            return $institution->users_count < 2;
        });

        return view('super_admin.admins.create', compact('institutions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:' . Setting::get('sec_min_password', 8),
            'institution_id' => 'required|exists:institutions,id',
        ], $this->validationMessages());

        $currentAdminCount = User::role('admin')->where('institution_id', $request->institution_id)->count();
        if ($currentAdminCount >= 2) {
            return redirect()->back()->with('error', __('Instansi ini sudah memiliki batas maksimal 2 admin sekolah.'))->withInput();
        }

        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'institution_id' => $request->institution_id,
        ]);

        $admin->assignRole('admin');

        AuditLogger::log('Menambah Admin Sekolah', 'users', $admin->id, $admin->institution_id, null, $admin->toArray());

        return redirect()->route('super-admin.admins.index')->with('success', __('Admin sekolah berhasil ditambahkan.'));
    }

    protected function validationMessages()
    {
        $email = request('email');

        if (!$email) {
            return [];
        }

        $existing = User::where('email', $email)->with('institution')->first();

        if ($existing) {
            $instansi = $existing->institution?->name ?? 'Instansi tidak diketahui';
            return [
                'email.unique' => __('Email ini sudah digunakan oleh ":name" di ":instansi". Silakan gunakan email yang berbeda.', [
                    'name' => $existing->name,
                    'instansi' => $instansi,
                ]),
            ];
        }

        return [
            'email.unique' => __('Email ini sudah digunakan oleh pengguna lain. Silakan gunakan email yang berbeda.'),
        ];
    }

    public function edit(User $admin)
    {
        $institutions = Institution::withCount(['users' => function($q) {
            $q->role('admin');
        }])->get()->filter(function($institution) use ($admin) {
            return $institution->id === $admin->institution_id || $institution->users_count < 2;
        });
        
        return view('super_admin.admins.edit', compact('admin', 'institutions'));
    }

    public function update(Request $request, User $admin)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $admin->id,
            'password'       => 'nullable|min:' . Setting::get('sec_min_password', 8) . '|confirmed',
            'institution_id' => 'required|exists:institutions,id',
        ], $this->validationMessages());

        if ($request->institution_id != $admin->institution_id) {
            $currentAdminCount = User::role('admin')->where('institution_id', $request->institution_id)->count();
            if ($currentAdminCount >= 2) {
                return redirect()->back()->with('error', __('Instansi tujuan sudah memiliki batas maksimal 2 admin sekolah.'))->withInput();
            }
        }

        $data = [
            'name'           => $request->name,
            'email'          => $request->email,
            'institution_id' => $request->institution_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
            $data['password_changed_at'] = now();
        }

        $old = $admin->toArray();
        $admin->update($data);

        AuditLogger::log('Mengubah Admin Sekolah', 'users', $admin->id, $admin->institution_id, $old, $admin->toArray());

        return redirect()->route('super-admin.admins.index')->with('success', __('Data admin sekolah berhasil diperbarui.'));
    }

    public function destroy(User $admin)
    {
        // Hitung sisa admin di instansi yang sama
        $remainingAdmins = User::role('admin')
            ->where('institution_id', $admin->institution_id)
            ->where('id', '!=', $admin->id)
            ->count();

        if ($remainingAdmins === 0) {
            return redirect()->back()->with('error', __('Tidak dapat menghapus admin terakhir. Pastikan ada minimal satu admin lain sebelum menghapus akun ini.'));
        }

        AuditLogger::log('Menghapus Admin Sekolah', 'users', $admin->id, $admin->institution_id, $admin->toArray(), null);

        $admin->delete();
        return redirect()->route('super-admin.admins.index')->with('success', __('Admin sekolah berhasil dihapus.'));
    }


    public function trash()
    {
        $admins = User::role('admin')->onlyTrashed()->get();
        return view('super_admin.admins.trash', compact('admins'));
    }

    public function restore(int|string $id)
    {
        $admin = User::onlyTrashed()->findOrFail($id);
        $admin->restore();

        AuditLogger::log('Memulihkan Admin Sekolah', 'users', $admin->id, $admin->institution_id, null, $admin->toArray());

        return redirect()->route('super-admin.admins.trash')->with('success', __('Admin sekolah berhasil dipulihkan.'));
    }

    public function forceDelete(int|string $id, Request $request)
    {
        $admin = User::onlyTrashed()->findOrFail($id);

        $agendaCount = $admin->agendas()->count();
        $scheduleCount = $admin->teachingSchedules()->count();
        $teacherStatusCount = TeacherStatus::where('teacher_id', $admin->id)->count();
        $homeroomCount = $admin->classHomeroom()->count();

        $affectsData = $agendaCount > 0 || $scheduleCount > 0
            || $teacherStatusCount > 0 || $homeroomCount > 0;

        if ($affectsData && ! $request->boolean('continue')) {
            return view('super_admin.admins.force-delete-confirm', compact(
                'admin', 'agendaCount', 'scheduleCount', 'teacherStatusCount', 'homeroomCount'
            ));
        }

        AuditLogger::log('Menghapus Permanen Admin Sekolah', 'users', $admin->id, $admin->institution_id, $admin->toArray(), null);

        $admin->forceDelete();

        return redirect()->route('super-admin.admins.trash')->with('success', __('Admin sekolah berhasil dihapus permanen.'));
    }
}
