<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\ClassHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClassPromotionController extends Controller
{
    /**
     * Show the class promotion form.
     */
    public function index()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        
        // Tampilkan tahun ajaran yang unik namanya saja (ambil ID tertinggi per nama)
        $academicYears = AcademicYear::orderBy('name', 'desc')->get()->unique('name');
        
        $classes = Classes::with('students')->orderBy('name')->get();
        
        $teachers = User::role('teacher')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'wakasek');
            })
            ->orderBy('name')
            ->get();

        return view('admin.class_promotions.index', compact('activeYear', 'academicYears', 'classes', 'teachers'));
    }

    /**
     * Preview students in a class for a given academic year.
     */
    public function preview(Request $request)
    {
        $classId = $request->input('class_id');
        $academicYearId = $request->input('academic_year_id');

        $students = User::whereHas('roles', function ($q) {
            $q->where('name', 'siswa');
        })->where('class_id', $classId)
        ->orderBy('name', 'asc')
        ->get();

        return response()->json([
            'students' => $students->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'nis' => $s->nis]),
        ]);
    }

    /**
     * Process the class promotion.
     */
    public function promote(Request $request)
    {
        $institutionId = Auth::user()?->institution_id;

        if (!$request->has('promotions') && $request->filled(['source_class_id', 'target_class_id'])) {
            $request->merge([
                'promotions' => [[
                    'source_class_id' => $request->input('source_class_id'),
                    'target_class_id' => $request->input('target_class_id'),
                    'new_homeroom_id' => $request->input('new_homeroom_id'),
                    'student_ids' => $request->input('student_ids', []),
                ]],
            ]);
        }

        $request->validate([
            'target_year_id'    => [
                'required',
                Rule::exists('academic_years', 'id')->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'promotions'        => 'required|array|min:1',
            'promotions.*.source_class_id' => [
                'required',
                Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'promotions.*.target_class_id' => [
                'required',
                Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'promotions.*.new_homeroom_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'promotions.*.student_ids'     => 'nullable|array',
            'promotions.*.student_ids.*'   => [
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
        ]);

        $targetYearId = $request->target_year_id;
        $promotions = $request->promotions;
        
        $sourceYear = AcademicYear::where('is_active', true)->first();

        if (!$sourceYear) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif!')->withInput();
        }

        if (strtolower($sourceYear->semester) !== 'genap') {
            return redirect()->back()->with('error', 'Kenaikan kelas hanya dapat dilakukan saat semester genap!')->withInput();
        }

        $targetYear = AcademicYear::findOrFail($targetYearId);
        
        // The target year must be strictly after the source year
        if ($targetYear->name <= $sourceYear->name) {
            return redirect()->back()->with('error', 'Tahun ajaran tujuan harus lebih baru dari tahun ajaran aktif saat ini!')->withInput();
        }

        // Validate all promotions first
        $gradeMapping = ['X' => 10, 'XI' => 11, 'XII' => 12];
        $totalStudentsPromoted = 0;
        $selectedNewHomeroomIds = [];

        foreach ($promotions as $promo) {
            $sourceClass = Classes::findOrFail($promo['source_class_id']);
            $targetClass = Classes::findOrFail($promo['target_class_id']);
            $studentIds = $promo['student_ids'] ?? [];
            $newHomeroomId = $promo['new_homeroom_id'] ?? null;
            
            if ($sourceClass->id === $targetClass->id) {
                return redirect()->back()->with('error', "Kelas {$sourceClass->name} tidak dapat dipindahkan ke kelas yang sama.")->withInput();
            }

            if ($sourceClass->major !== $targetClass->major) {
                return redirect()->back()->with('error', "Siswa dari {$sourceClass->name} tidak dapat dipindahkan ke {$targetClass->name} karena berbeda jurusan!")->withInput();
            }

            $sourceLevel = $gradeMapping[$sourceClass->grade_level] ?? 0;
            $targetLevel = $gradeMapping[$targetClass->grade_level] ?? 0;

            if ($sourceLevel == 12) {
                return redirect()->back()->with('error', "Siswa kelas {$sourceClass->name} (XII) tidak dapat melakukan kenaikan kelas. Silakan gunakan menu Kelulusan.")->withInput();
            }

            if ($sourceLevel == 10 && $targetLevel != 11) {
                return redirect()->back()->with('error', "Siswa kelas {$sourceClass->name} (X) hanya dapat dinaikkan ke kelas XI.")->withInput();
            }

            if ($sourceLevel == 11 && $targetLevel != 12) {
                return redirect()->back()->with('error', "Siswa kelas {$sourceClass->name} (XI) hanya dapat dinaikkan ke kelas XII.")->withInput();
            }

            // Pastikan kelas tujuan masih KOSONG (tidak ada siswa aktif)
            if ($targetClass->students()->count() > 0) {
                return redirect()->back()->with('error', "Kelas tujuan ({$targetClass->name}) masih memiliki siswa aktif! Harap kosongkan terlebih dahulu.")->withInput();
            }
            
            $totalStudentsPromoted += count($studentIds);

            if ($newHomeroomId) {
                if (in_array((int) $newHomeroomId, $selectedNewHomeroomIds, true)) {
                    return redirect()->back()->with('error', 'Guru wali kelas baru tidak boleh dipilih untuk lebih dari satu kelas tujuan.')->withInput();
                }
                $selectedNewHomeroomIds[] = (int) $newHomeroomId;

                $activeStudentCount = User::role('siswa')
                    ->where('class_id', $sourceClass->id)
                    ->where('status', '!=', 'graduated')
                    ->count();
                $sourceWillBeEmpty = count($studentIds) >= $activeStudentCount;

                $conflictingClass = Classes::where('homeroom_teacher_id', $newHomeroomId)
                    ->where('id', '!=', $targetClass->id)
                    ->where(function ($query) use ($sourceClass, $sourceWillBeEmpty) {
                        if ($sourceWillBeEmpty) {
                            $query->where('id', '!=', $sourceClass->id);
                        }
                    })
                    ->first();

                if ($conflictingClass) {
                    return redirect()->back()
                        ->with('error', "Guru wali kelas baru sudah menjadi wali kelas aktif di {$conflictingClass->name}.")
                        ->withInput();
                }
            }
        }
        
        if ($totalStudentsPromoted === 0) {
             return redirect()->back()->with('error', 'Tidak ada siswa yang dipilih untuk dinaikkan kelas.')->withInput();
        }

        DB::transaction(function () use ($promotions, $targetYearId, $sourceYear, $targetYear) {
            foreach ($promotions as $promo) {
                $sourceClassId  = $promo['source_class_id'];
                $targetClassId  = $promo['target_class_id'];
                $newHomeroomId  = $promo['new_homeroom_id'] ?? null;
                $studentIds     = $promo['student_ids'] ?? [];
                
                if (empty($studentIds)) continue; // Skip if no students are promoted for this class

                $sourceClass = Classes::find($sourceClassId);
                $targetClass = Classes::find($targetClassId);
                $oldHomeroomId = $targetClass->homeroom_teacher_id;
                $sourceHomeroomId = $sourceClass->homeroom_teacher_id;

                foreach ($studentIds as $studentId) {
                    // 1. Snapshot kelas LAMA di tahun ajaran SEKARANG (source)
                    if ($sourceYear) {
                        ClassHistory::updateOrCreate([
                            'user_id'          => $studentId,
                            'academic_year_id' => $sourceYear->id,
                        ], [
                            'class_id'            => $sourceClassId,
                            'homeroom_teacher_id' => $sourceClass->homeroom_teacher_id,
                        ]);
                    }

                    // 2. Snapshot kelas BARU di tahun ajaran TUJUAN
                    ClassHistory::updateOrCreate([
                        'user_id'          => $studentId,
                        'academic_year_id' => $targetYearId,
                    ], [
                        'class_id'            => $targetClassId,
                        'homeroom_teacher_id' => $newHomeroomId ?? $targetClass->homeroom_teacher_id,
                    ]);

                    // 3. Update the student's current class_id
                    User::where('id', $studentId)->update(['class_id' => $targetClassId]);
                }

                // 3b. Snapshot riwayat siswa yang TIDAK naik kelas (tetap di kelas asal)
                User::role('siswa')
                    ->where('class_id', $sourceClassId)
                    ->where('status', '!=', 'graduated')
                    ->whereNotIn('id', $studentIds)
                    ->get()
                    ->each(function ($student) use ($sourceYear, $targetYearId, $sourceClassId, $sourceClass) {
                        if ($sourceYear) {
                            ClassHistory::updateOrCreate([
                                'user_id'          => $student->id,
                                'academic_year_id' => $sourceYear->id,
                            ], [
                                'class_id'            => $sourceClassId,
                                'homeroom_teacher_id' => $sourceClass->homeroom_teacher_id,
                            ]);
                        }

                        ClassHistory::updateOrCreate([
                            'user_id'          => $student->id,
                            'academic_year_id' => $targetYearId,
                        ], [
                            'class_id'            => $sourceClassId,
                            'homeroom_teacher_id' => $sourceClass->homeroom_teacher_id,
                        ]);
                    });

                // 4b. Sekretaris yang ikut naik kelas (hanya jika terpilih)
                User::role('sekretaris')
                    ->where('class_id', $sourceClassId)
                    ->whereIn('id', $studentIds)
                    ->update(['class_id' => $targetClassId]);

                // 4. Update academic_year (sebagai penanda angkatan) and homeroom teacher for the target class
                $updateData = ['academic_year' => $sourceClass->academic_year];
                
                if ($newHomeroomId && $newHomeroomId != $oldHomeroomId) {
                    $updateData['homeroom_teacher_id'] = $newHomeroomId;
                }
                
                Classes::where('id', $targetClassId)->update($updateData);

                if ($sourceClass->students()->where('status', '!=', 'graduated')->count() === 0) {
                    Classes::where('id', $sourceClassId)->update(['homeroom_teacher_id' => null]);
                }
                
                if ($newHomeroomId && $newHomeroomId != $oldHomeroomId) {
                    // Add role to new teacher
                    $newTeacher = User::find($newHomeroomId);
                    if ($newTeacher && !$newTeacher->hasRole('wali_kelas')) {
                        $newTeacher->assignRole('wali_kelas');
                    }
                    
                    // Remove role from old teacher if they are no longer a homeroom teacher
                    if ($oldHomeroomId) {
                        $oldTeacher = User::find($oldHomeroomId);
                        if ($oldTeacher
                            && !Classes::where('homeroom_teacher_id', $oldHomeroomId)->exists()
                            && !ClassHistory::where('homeroom_teacher_id', $oldHomeroomId)->exists()) {
                            $oldTeacher->removeRole('wali_kelas');
                        }
                    }
                }

                if ($sourceHomeroomId && $sourceHomeroomId != $newHomeroomId) {
                    $sourceTeacher = User::find($sourceHomeroomId);
                    if ($sourceTeacher
                        && !Classes::where('homeroom_teacher_id', $sourceHomeroomId)->exists()
                        && !ClassHistory::where('homeroom_teacher_id', $sourceHomeroomId)->exists()) {
                        $sourceTeacher->removeRole('wali_kelas');
                    }
                }
            }
        });

        return redirect()->route('admin.class-promotions.index')
            ->with('success', $totalStudentsPromoted . ' siswa berhasil dipindahkan ke tingkat berikutnya (T.A. ' . $targetYear->name . ').');
    }
}
