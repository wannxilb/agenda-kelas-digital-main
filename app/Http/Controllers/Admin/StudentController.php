<?php
// app/Http/Controllers/Admin/StudentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Classes;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use App\Exports\StudentsDataExport;
use App\Exports\StudentsTemplateExport;
use App\Models\Setting;
use App\Models\Institution;

class StudentController extends Controller
{
    private function clearStudentStatsCache(): void
    {
        $institutionId = Auth::user()?->institution_id;
        if (!$institutionId) return;
        foreach (['active', 'inactive', 'graduated', 'all'] as $status) {
            Cache::forget('stats_male_students_' . $status . '_' . $institutionId);
            Cache::forget('stats_female_students_' . $status . '_' . $institutionId);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Optimasi query dengan select kolom yang dibutuhkan saja
        $query = User::role('siswa')
            ->select('id', 'name', 'nis', 'nisn', 'gender', 'class_id', 'status', 'phone', 'email', 'tempat_lahir', 'tanggal_lahir', 'address', 'rt', 'rw', 'kelurahan', 'kecamatan')
            ->with(['class' => function($q) {
                $q->select('id', 'name', 'academic_year');
            }]);
        
        // Filter berdasarkan kelas
        if ($request->has('class_id') && $request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        
        // Filter berdasarkan gender
        if ($request->has('gender') && $request->gender) {
            $query->where('gender', $request->gender);
        }
        
        // Filter berdasarkan status
        $status = $request->input('status', 'active');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filter berdasarkan jurusan
        if ($request->has('major') && $request->major) {
            $query->whereHas('class', function($q) use ($request) {
                $q->where('major', $request->major);
            });
        }
        
        // Filter berdasarkan tingkat
        if ($request->has('grade_level') && $request->grade_level) {
            $query->whereHas('class', function($q) use ($request) {
                $q->where('grade_level', $request->grade_level);
            });
        }
        
        // Search dengan optimasi indeks (hanya jika input minimal 2 karakter)
        if ($request->has('search') && $request->search && strlen($request->search) >= 2) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($searchTerm) . '%'])
                  ->orWhereRaw('LOWER(nis) LIKE ?', ['%' . mb_strtolower($searchTerm) . '%'])
                  ->orWhereRaw('LOWER(nisn) LIKE ?', ['%' . mb_strtolower($searchTerm) . '%']);
            });
        }
        
        // Menampilkan 100 data per halaman secara default
        $perPage = 100;
        
        // Urutkan alfabet global agar daftar siswa mudah dipindai.
        $students = $query->orderByRaw('LOWER(name) ASC')->orderBy('class_id')->paginate($perPage);
        
        // Load statistik sesuai tab status
        $classList = Classes::select('id', 'name', 'academic_year')->orderBy('name')->get();
        $institutionId = Auth::user()?->institution_id ?? 'all';
        $maleCount = cache()->remember('stats_male_students_' . $status . '_' . $institutionId, 3600, function() use ($status) {
            return User::role('siswa')->where('gender', 'L')->where('status', $status)->count();
        });
        $femaleCount = cache()->remember('stats_female_students_' . $status . '_' . $institutionId, 3600, function() use ($status) {
            return User::role('siswa')->where('gender', 'P')->where('status', $status)->count();
        });
        
        // Ambil data filter untuk dropdown
        $majors = Classes::select('major')->distinct()->whereNotNull('major')->pluck('major');
        $gradeLevels = ['X', 'XI', 'XII']; // Standard grade levels
        
        return view('admin.students.index', compact('students', 'classList', 'maleCount', 'femaleCount', 'perPage', 'status', 'majors', 'gradeLevels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classes = Classes::orderBy('name')->get();
        return view('admin.students.create', compact('classes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $institutionId = Auth::user()?->institution_id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nis' => [
                'required', 'string',
                Rule::unique('users', 'nis')->where(fn($q) => $q->where('institution_id', $institutionId)),
            ],
            'gender' => 'required|in:L,P',
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'phone' => 'nullable|string|max:15',
            'parent_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => ['required', 'string', 'min:' . Setting::get('sec_min_password', 8), Password::defaults()],
            'nisn' => [
                'nullable', 'string', 'max:20',
                Rule::unique('users', 'nisn')->where(fn($q) => $q->where('institution_id', $institutionId)),
            ],
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'kelurahan' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $student = User::create([
            'name' => $request->name,
            'email' => $request->nis . '@agenda.local', // Auto-generate internal email
            'nis' => $request->nis,
            'gender' => $request->gender,
            'class_id' => $request->class_id,
            'status' => 'active',
            'phone' => $request->phone,
            'parent_phone' => $request->parent_phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'email_verified_at' => now(),
            'nisn' => $request->nisn,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'rt' => $request->rt,
            'rw' => $request->rw,
            'kelurahan' => $request->kelurahan,
            'kecamatan' => $request->kecamatan,
        ]);
        
        $student->assignRole('siswa');

        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        if ($activeYear) {
            $class = \App\Models\Classes::find($request->class_id);
            if ($class) {
                \App\Models\ClassHistory::create([
                    'user_id' => $student->id,
                    'academic_year_id' => $activeYear->id,
                    'class_id' => $class->id,
                    'homeroom_teacher_id' => $class->homeroom_teacher_id,
                ]);
            }
        }

        $this->clearStudentStatsCache();

        $prefix = $request->segment(1);
        return redirect()->route($prefix . '.students.index')
            ->with('success', 'Siswa berhasil ditambahkan!');
    }

    /**
     * Display the student history.
     */
    public function history(User $student)
    {
        $student->load(['classHistories.class.homeroomTeacher', 'classHistories.academicYear']);
        return view('admin.students.history', compact('student'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $student)
    {
        $student->load(['class', 'attendances', 'classHistories.class.homeroomTeacher', 'classHistories.academicYear']);

        $resolved = app(\App\Services\AttendanceSummaryService::class)
            ->resolveForRange([$student->id], '1970-01-01', now()->toDateString());
        $counts = app(\App\Services\AttendanceSummaryService::class)->countPerStudent($resolved)[$student->id]
            ?? ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'sick' => 0];

        // Statistik presensi (manual + digital)
        $attendance_stats = [
            'present' => $counts['present'],
            'absent' => $counts['absent'],
            'late' => $counts['late'],
            'excused' => $counts['excused'],
            'sick' => $counts['sick'],
        ];

        $manual = $student->attendances()->get()->keyBy(fn ($att) => $att->date->toDateString());
        $digital = \App\Models\StudentDailyAttendance::where('student_id', $student->id)
            ->get()
            ->keyBy(fn ($att) => $att->date->toDateString());

        $recentAttendance = collect($resolved[$student->id] ?? [])
            ->sortKeysDesc()
            ->take(10)
            ->map(function ($status, $date) use ($manual, $digital) {
                $att = $manual->get($date);
                $digitalAtt = $digital->get($date);

                return (object) [
                    'date' => $date,
                    'status' => $status,
                    'check_in_time' => $att?->check_in_time ?? ($digitalAtt?->check_in_at?->format('H:i:s')),
                    'note' => $att?->note,
                ];
            })
            ->values();

        return view('admin.students.show', compact('student', 'attendance_stats', 'recentAttendance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $student)
    {
        $classes = Classes::orderBy('name')->get();
        return view('admin.students.edit', compact('student', 'classes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $student)
    {
        $institutionId = Auth::user()?->institution_id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nis' => [
                'required', 'string',
                Rule::unique('users', 'nis')->ignore($student->id)->where(fn($q) => $q->where('institution_id', $institutionId)),
            ],
            'gender' => 'required|in:L,P',
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'status' => 'required|in:active,inactive,graduated',
            'phone' => 'nullable|string|max:15',
            'parent_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'password' => ['nullable', 'string', 'min:' . Setting::get('sec_min_password', 8), Password::defaults()],
            'nisn' => [
                'nullable', 'string', 'max:20',
                Rule::unique('users', 'nisn')->ignore($student->id)->where(fn($q) => $q->where('institution_id', $institutionId)),
            ],
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'kelurahan' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->status === 'graduated') {
                $class = Classes::find($request->class_id);
                if ($class && $class->grade_level !== 'XII') {
                    $validator->errors()->add('status', 'Siswa tingkat kelas ' . $class->grade_level . ' tidak dapat diubah statusnya menjadi Lulus. Hanya siswa tingkat XII yang dapat diluluskan.');
                }

                $activeYear = AcademicYear::where('is_active', true)->first();
                if (strtolower($activeYear->semester ?? '') !== 'genap') {
                    $validator->errors()->add('status', 'Kelulusan hanya dapat dilakukan saat semester genap!');
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['password', 'email', 'institution_id']);
        $data['email'] = $request->nis . '@agenda.local'; // Keep sync with NIS
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['password_changed_at'] = now();
        }
        
        $student->update($data);
        
        // Handle Role Sekretaris
        if ($request->has('is_secretary')) {
            if (!$student->hasRole('sekretaris')) {
                $student->assignRole('sekretaris');
            }
        } else {
            if ($student->hasRole('sekretaris')) {
                $student->removeRole('sekretaris');
            }
        }

        // Jangan timpa riwayat kelas yang sudah tercatat untuk tahun ajaran
        // aktif (mis. hasil promosi/kelulusan). Edit data siswa hanya boleh
        // membuat riwayat apabila belum ada untuk tahun ajaran tersebut.
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        if ($activeYear) {
            $class = \App\Models\Classes::find($request->class_id);
            if ($class) {
                $hasHistory = \App\Models\ClassHistory::where('user_id', $student->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->exists();

                if (!$hasHistory) {
                    \App\Models\ClassHistory::create([
                        'user_id' => $student->id,
                        'academic_year_id' => $activeYear->id,
                        'class_id' => $class->id,
                        'homeroom_teacher_id' => $class->homeroom_teacher_id,
                    ]);
                }
            }
        }

        $this->clearStudentStatsCache();

        $message = 'Siswa berhasil diperbarui!';
        if ($request->filled('password')) {
            $message .= ' Password telah berhasil diubah.';
        }

        $prefix = $request->segment(1);
        return redirect()->route($prefix . '.students.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $student)
    {
        $student->delete();
        
        $this->clearStudentStatsCache();

        $prefix = request()->segment(1);
        return redirect()->route($prefix . '.students.index')
            ->with('success', 'Siswa berhasil dihapus!');
    }

    /**
     * Import students from Excel/CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'class_id' => [
                'nullable',
                Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institution_id', Auth::user()?->institution_id)),
            ],
        ]);

        try {
            $importedCount = 0;
            $skippedCount = 0;

            DB::transaction(function () use ($request, &$importedCount, &$skippedCount) {
                $import = new StudentsImport($request->class_id, Auth::user()?->institution_id);
                Excel::import($import, $request->file('file'));
                
                $importedCount = $import->getImportedCount();
                $skippedCount = $import->getSkippedCount();
            });
            
            $this->clearStudentStatsCache();
            
            $message = "Data siswa berhasil diimport! Berhasil menyimpan {$importedCount} siswa.";
            if ($skippedCount > 0) {
                $message .= " ({$skippedCount} siswa dilewati karena NIS sudah terdaftar sebelumnya)";
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    /**
     * Export template for import
     */
    public function exportTemplate()
    {
        return Excel::download(new StudentsTemplateExport, 'template_siswa.xlsx');
    }

    /**
     * Export seluruh data siswa (termasuk No WA Orang Tua)
     */
    public function exportData(Request $request)
    {
        $institutionId = Auth::user()?->institution_id;
        $classId = $request->input('class_id');

        return Excel::download(
            new StudentsDataExport($institutionId, $classId),
            'data_siswa_'.now()->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * Bulk graduation form
     */
    public function bulkGraduation(Request $request)
    {
        $institutionId = Auth::user()?->institution_id;
        $classes = Classes::where('grade_level', 'XII')
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->with(['students' => function($q) {
                $q->where('status', 'active')->orderByRaw('LOWER(name) ASC');
            }, 'homeroomTeacher'])
            ->orderBy('name')
            ->get();
            
        $totalStudents = $classes->sum(function($class) { return $class->students->count(); });
        
        return view('admin.students.bulk-graduation', compact('classes', 'totalStudents'));
    }

    /**
     * Process bulk graduation
     */
    public function processBulkGraduation(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => [
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('institution_id', Auth::user()?->institution_id)),
            ],
        ]);

        $students = User::role('siswa')
            ->whereIn('id', $request->student_ids)
            ->whereHas('class', function($query) {
                $query->where('grade_level', 'XII');
            })
            ->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada siswa kelas XII yang valid untuk diluluskan.');
        }

        $activeYear = AcademicYear::where('is_active', true)->first();

        if (strtolower($activeYear->semester ?? '') !== 'genap') {
            return redirect()->back()->with('error', 'Kelulusan hanya dapat dilakukan saat semester genap!')->withInput();
        }

        DB::transaction(function () use ($students, $activeYear) {
            foreach ($students as $student) {
                // 1. Snapshot riwayat kelas XII di tahun ajaran aktif
                if ($activeYear) {
                    \App\Models\ClassHistory::updateOrCreate([
                        'user_id'          => $student->id,
                        'academic_year_id' => $activeYear->id,
                    ], [
                        'class_id'            => $student->class_id,
                        'homeroom_teacher_id' => $student->class->homeroom_teacher_id ?? null,
                    ]);
                }

                // 2. Ubah status menjadi lulus
                $student->update(['status' => 'graduated']);
            }
        });

        $this->clearStudentStatsCache();

        $prefix = $request->segment(1);
        return redirect()->route($prefix . '.students.index')
            ->with('success', count($students) . ' siswa berhasil dinyatakan lulus!');
    }

    /**
     * Bulk delete selected students
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => [
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('institution_id', Auth::user()?->institution_id)),
            ],
        ]);

        $students = User::role('siswa')->whereIn('id', $request->student_ids)->get();
        $count = $students->count();

        foreach ($students as $student) {
            $student->removeRole('siswa');
            $student->delete();
        }

        $this->clearStudentStatsCache();

        $prefix = request()->segment(1);
        return redirect()->route($prefix . '.students.index')
            ->with('success', "{$count} siswa berhasil dihapus!");
    }
}
