<?php
// app/Http/Controllers/Admin/TeacherController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TeachersImport;
use App\Exports\TeachersTemplateExport;
use App\Models\Setting;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil semua kelas untuk filter "Guru yang mengajar di kelas..."
        $allClasses = \App\Models\Classes::orderBy('name')->get();

        // Query dasar guru
        $query = User::role('teacher')
            ->select('id', 'name', 'email', 'nip', 'phone', 'address', 'created_at')
            ->with(['subjects' => function($q) {
                $q->select('subjects.id', 'subjects.name');
            }])
            ->withCount('agendas')
            ->withCount(['agendas as monthly_agendas_count' => function($q) {
                $q->whereMonth('date', now()->month)
                  ->whereYear('date', now()->year);
            }]);
        
        // FILTER: Guru yang mengajar di Kelas tertentu (berdasarkan jadwal)
        if ($request->filled('class_id')) {
            $query->whereHas('teachingSchedules', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        // Search berdasarkan Nama/NIP/Email
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($searchTerm) . '%'])
                  ->orWhereRaw('LOWER(nip) LIKE ?', ['%' . mb_strtolower($searchTerm) . '%'])
                  ->orWhereRaw('LOWER(email) LIKE ?', ['%' . mb_strtolower($searchTerm) . '%']);
            });
        }
        
        $perPage = $request->has('per_page') ? (int)$request->per_page : 50;
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 50;
        
        $teachers = $query->orderBy('name', 'asc')->paginate($perPage);
        
        return view('admin.teachers.index', compact('teachers', 'perPage', 'allClasses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.teachers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $institutionId = Auth::user()?->institution_id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required', 'email',
                Rule::unique('users', 'email')->where(fn($q) => $q->where('institution_id', $institutionId)),
            ],
            'nip' => [
                'required', 'string',
                Rule::unique('users', 'nip')->where(fn($q) => $q->where('institution_id', $institutionId)),
            ],
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:500',
            'password' => ['required', 'string', 'min:' . Setting::get('sec_min_password', 8), Password::defaults()],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $teacher = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nip' => $request->nip,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'email_verified_at' => now(),
            'is_wakasek' => $request->has('is_wakasek') ? true : false,
            'institution_id' => $institutionId,
        ]);
        
        $teacher->assignRole('teacher');
        
        if ($request->has('is_wakasek')) {
            $teacher->assignRole('wakasek');
        }

        $prefix = $request->segment(1);
        return redirect()->route($prefix . '.teachers.index')
            ->with('success', 'Guru berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $teacher)
    {
        $teacher->load(['subjects', 'agendas', 'classHomeroom']);
        
        // Statistik mengajar
        $teaching_stats = [
            'total_subjects' => $teacher->subjects()->count(),
            'total_agendas' => $teacher->agendas()->count(),
            'monthly_agendas' => $teacher->agendas()
                ->whereMonth('date', date('m'))
                ->count(),
        ];
        
        return view('admin.teachers.show', compact('teacher', 'teaching_stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $teacher)
    {
        return view('admin.teachers.edit', compact('teacher'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $teacher)
    {
        $institutionId = Auth::user()?->institution_id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required', 'email',
                Rule::unique('users', 'email')->ignore($teacher->id)->where(fn($q) => $q->where('institution_id', $institutionId)),
            ],
            'nip' => [
                'required', 'string',
                Rule::unique('users', 'nip')->ignore($teacher->id)->where(fn($q) => $q->where('institution_id', $institutionId)),
            ],
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:500',
            'password' => ['nullable', 'string', 'min:' . Setting::get('sec_min_password', 8), Password::defaults()]
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except('password', 'is_wakasek', 'institution_id');
        
        // Handle is_wakasek checkbox
        if ($teacher->classHomeroom()->count() == 0) {
            $is_wakasek = $request->has('is_wakasek') ? true : false;
            $data['is_wakasek'] = $is_wakasek;

            if ($is_wakasek) {
                $teacher->assignRole('wakasek');
            } else {
                $teacher->removeRole('wakasek');
            }
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['password_changed_at'] = now();
        }
        
        $teacher->update($data);

        $message = 'Guru berhasil diperbarui!';
        if ($request->filled('password')) {
            $message .= ' Password telah berhasil diubah.';
        }

        $prefix = $request->segment(1);
        return redirect()->route($prefix . '.teachers.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $teacher)
    {
        // Cek apakah guru masih mengajar
        if ($teacher->subjects()->count() > 0) {
            $prefix = request()->segment(1);
            return redirect()->route($prefix . '.teachers.index')
                ->with('error', 'Guru tidak dapat dihapus karena masih mengajar mata pelajaran!');
        }
        
        if ($teacher->classHomeroom()->count() > 0) {
            $prefix = request()->segment(1);
            return redirect()->route($prefix . '.teachers.index')
                ->with('error', 'Guru tidak dapat dihapus karena menjadi wali kelas!');
        }

        $teacher->delete();

        $prefix = request()->segment(1);
        return redirect()->route($prefix . '.teachers.index')
            ->with('success', 'Guru berhasil dihapus!');
    }

    /**
     * Import teachers from Excel/CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            Excel::import(new TeachersImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data guru berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    /**
     * Export template for import
     */
    public function exportTemplate()
    {
        return Excel::download(new TeachersTemplateExport, 'template_guru.xlsx');
    }
}
