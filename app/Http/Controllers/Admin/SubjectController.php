<?php
// app/Http/Controllers/Admin/SubjectController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use App\Imports\SubjectsImport;
use App\Exports\SubjectsTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $teachers = User::role('teacher')->orderBy('name')->get();
        
        $query = Subject::with(['teachers' => function($q) {
                $q->select('users.id', 'users.name');
            }])
            ->withCount(['schedules', 'agendas']);
        
        if ($request->filled('teacher_id')) {
            $query->whereHas('teachers', function($q) use ($request) {
                $q->where('users.id', $request->teacher_id);
            });
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($searchTerm) . '%']);
            });
        }
        
        $perPage = $request->has('per_page') ? (int)$request->per_page : 25;
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 25;

        $subjects = $query->latest()->paginate($perPage);
        
        return view('admin.subjects.index', compact('subjects', 'teachers', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $teachers = User::role('teacher')->get();
        return view('admin.subjects.create', compact('teachers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $institutionId = Auth::user()?->institution_id;
        $validator = Validator::make($request->all(), [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('subjects', 'name')->where(fn($q) => $q->where('institution_id', $institutionId)),
            ],
            'teacher_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'credit_hours' => 'required|integer|min:1|max:8',
            'description' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $subject = Subject::create($request->except('teacher_id'));
        $subject->teachers()->sync([$request->teacher_id]);

        $prefix = $request->segment(1);
        return redirect()->route($prefix . '.subjects.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subject $subject)
    {
        $subject->load(['teachers', 'schedules.class']);
        
        $stats = [
            'total_classes' => $subject->schedules()->distinct('class_id')->count('class_id'),
            'total_schedules' => $subject->schedules()->count(),
            'total_agendas' => $subject->agendas()->count(),
        ];

        $allTeachers = User::role('teacher')
            ->where('institution_id', Auth::user()?->institution_id)
            ->whereNotIn('id', $subject->teachers->pluck('id'))
            ->orderBy('name')
            ->get();
        
        return view('admin.subjects.show', compact('subject', 'stats', 'allTeachers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subject $subject)
    {
        $teachers = User::role('teacher')->get();
        return view('admin.subjects.edit', compact('subject', 'teachers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        $institutionId = Auth::user()?->institution_id;
        $validator = Validator::make($request->all(), [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('subjects', 'name')->ignore($subject->id)->where(fn($q) => $q->where('institution_id', $institutionId)),
            ],
            'credit_hours' => 'required|integer|min:1|max:8',
            'description' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $subject->update($request->only('name', 'credit_hours', 'description'));

        $prefix = $request->segment(1);
        return redirect()->route($prefix . '.subjects.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject)
    {
        if ($subject->schedules()->count() > 0) {
            $prefix = request()->segment(1);
            return redirect()->route($prefix . '.subjects.index')
                ->with('error', 'Mata pelajaran tidak dapat dihapus karena masih memiliki jadwal!');
        }

        $subject->teachers()->detach();
        $subject->delete();

        $prefix = request()->segment(1);
        return redirect()->route($prefix . '.subjects.index')
            ->with('success', 'Mata pelajaran berhasil dihapus!');
    }

    /**
     * Add a teacher to a subject.
     */
    public function addTeacher(Request $request, Subject $subject)
    {
        $institutionId = Auth::user()?->institution_id;
        $request->validate([
            'teacher_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
        ]);

        if ($subject->teachers()->where('user_id', $request->teacher_id)->exists()) {
            return redirect()->back()->with('error', 'Guru sudah terdaftar di mata pelajaran ini!');
        }

        $subject->teachers()->attach($request->teacher_id);

        $prefix = $request->segment(1);
        return redirect()->route($prefix . '.subjects.show', $subject)
            ->with('success', 'Guru berhasil ditambahkan ke mata pelajaran!');
    }

    /**
     * Remove a teacher from a subject.
     */
    public function removeTeacher(Request $request, Subject $subject, int|string $teacherId)
    {
        if ($subject->teachers()->count() <= 1) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus guru terakhir dari mata pelajaran!');
        }

        $subject->teachers()->detach($teacherId);

        $prefix = $request->segment(1);
        return redirect()->route($prefix . '.subjects.show', $subject)
            ->with('success', 'Guru berhasil dihapus dari mata pelajaran!');
    }

    /**
     * Import subjects from Excel/CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            $import = new SubjectsImport();
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $skipped  = $import->getSkippedCount();
            $msg = "Berhasil mengimpor {$imported} mata pelajaran.";
            if ($skipped > 0) {
                $msg .= " {$skipped} baris dilewati (sudah ada atau data tidak lengkap).";
            }
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    /**
     * Export template for subjects import
     */
    public function exportTemplate()
    {
        return Excel::download(new SubjectsTemplateExport, 'template_mata_pelajaran.xlsx');
    }
}
