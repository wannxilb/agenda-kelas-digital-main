<?php
// app/Http/Controllers/Admin/ClassController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\User;
use App\Http\Requests\StoreClassRequest;
use App\Http\Requests\UpdateClassRequest;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil data untuk filter
        $majors = Classes::distinct()->pluck('major')->filter()->values();
        $gradeLevels = Classes::distinct()->pluck('grade_level')->filter()->values();
        $teachers = User::role('wali_kelas')->orderBy('name')->get();

        // Optimasi query dengan select kolom yang dibutuhkan dan eager loading
        $query = Classes::with(['homeroomTeacher' => function($q) {
                $q->select('id', 'name', 'nip');
            }])
            ->select('id', 'name', 'major', 'grade_level', 'academic_year', 'homeroom_teacher_id', 'capacity', 'description', 'is_active')
            ->withCount('students');

        // Filter Berdasarkan Wali Kelas
        if ($request->filled('teacher_id')) {
            $query->where('homeroom_teacher_id', $request->teacher_id);
        }

        // Filter Berdasarkan Jurusan
        if ($request->filled('major')) {
            $query->where('major', $request->major);
        }

        // Filter Berdasarkan Tingkat
        if ($request->filled('grade_level')) {
            $query->where('grade_level', $request->grade_level);
        }

        // Search berdasarkan Nama Kelas
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereRaw('LOWER(name) LIKE ?', ["%" . mb_strtolower($search) . "%"]);
        }

        // Pagination dinamis
        $perPage = $request->has('per_page') ? (int)$request->per_page : 25;
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 25;

        $classList = $query->orderBy('academic_year', 'desc')
                          ->orderBy('grade_level', 'asc')
                          ->orderBy('name', 'asc')
                          ->paginate($perPage);
            
        return view('admin.classes.index', compact('classList', 'perPage', 'majors', 'gradeLevels', 'teachers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $assignedTeacherIds = Classes::whereNotNull('homeroom_teacher_id')
            ->where('is_active', true)
            ->pluck('homeroom_teacher_id');

        $teachers = User::role('teacher')
            ->whereNotIn('id', $assignedTeacherIds)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'wakasek');
            })
            ->get();
            
        $academicYears = \App\Models\AcademicYear::select('name')->distinct()->orderBy('name', 'desc')->pluck('name');

        $majors = \App\Models\Major::with('gradeTemplates')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.classes.create', compact('teachers', 'academicYears', 'majors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClassRequest $request)
    {
        Classes::create($request->validated());

        // Assign wali_kelas role to the teacher
        if ($request->homeroom_teacher_id) {
            $teacher = User::find($request->homeroom_teacher_id);
            if ($teacher && !$teacher->hasRole('wali_kelas')) {
                $teacher->assignRole('wali_kelas');
            }
        }

        $prefix = $request->segment(1);
        return redirect()->route($prefix . '.classes.index')
            ->with('success', 'Kelas berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Classes|int|string $class)
    {
        if (!$class instanceof Classes) {
            $class = Classes::findOrFail($class);
        }
        $class->load(['homeroomTeacher', 'students', 'schedules.subject', 'agendas.teacher'])->loadCount('students');
        return view('admin.classes.show', compact('class'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Classes|int|string $class)
    {
        if (!$class instanceof Classes) {
            $class = Classes::findOrFail($class);
        }
        $class->loadCount('students');
        
        $assignedTeacherIds = Classes::whereNotNull('homeroom_teacher_id')
            ->where('is_active', true)
            ->where('id', '!=', $class->id)
            ->pluck('homeroom_teacher_id');

        $teachers = User::role('teacher')
            ->whereNotIn('id', $assignedTeacherIds)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'wakasek');
            })
            ->get();
            
        $academicYears = \App\Models\AcademicYear::select('name')->distinct()->orderBy('name', 'desc')->pluck('name');

        $majors = \App\Models\Major::with('gradeTemplates')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.classes.edit', compact('class', 'teachers', 'academicYears', 'majors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClassRequest $request, Classes|int|string $class)
    {
        if (!$class instanceof Classes) {
            $class = Classes::findOrFail($class);
        }

        $oldTeacherId = $class->homeroom_teacher_id;
        $class->update($request->validated());

        // Handle role changes
        if ($oldTeacherId != $request->homeroom_teacher_id) {
            // Remove role from old teacher if they are no longer a homeroom teacher for any class
            if ($oldTeacherId) {
                $oldTeacher = User::find($oldTeacherId);
                // Check if the old teacher is a homeroom teacher for any OTHER active class
                if ($oldTeacher && !Classes::where('homeroom_teacher_id', $oldTeacherId)->where('id', '!=', $class->id)->exists()) {
                    $oldTeacher->removeRole('wali_kelas');
                }
            }

            // Add role to new teacher
            if ($request->homeroom_teacher_id) {
                $newTeacher = User::find($request->homeroom_teacher_id);
                if ($newTeacher && !$newTeacher->hasRole('wali_kelas')) {
                    $newTeacher->assignRole('wali_kelas');
                }
            }
        }

        $prefix = $request->segment(1);
        return redirect()->route($prefix . '.classes.index')
            ->with('success', 'Kelas berhasil diperbarui!');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classes|int|string $class)
    {
        if (!$class instanceof Classes) {
            $class = Classes::findOrFail($class);
        }
        // Cek apakah kelas memiliki siswa
        if ($class->students()->count() > 0) {
            $prefix = request()->segment(1);
            return redirect()->route($prefix . '.classes.index')
                ->with('error', 'Kelas tidak dapat dihapus karena masih memiliki siswa!');
        }

        $teacherId = $class->homeroom_teacher_id;
        $class->delete();

        // Remove role from teacher if they are no longer a homeroom teacher for any class
        if ($teacherId) {
            $teacher = User::find($teacherId);
            if ($teacher && !Classes::where('homeroom_teacher_id', $teacherId)->exists()) {
                $teacher->removeRole('wali_kelas');
            }
        }

        $prefix = request()->segment(1);
        return redirect()->route($prefix . '.classes.index')
            ->with('success', 'Kelas berhasil dihapus!');
    }
}
