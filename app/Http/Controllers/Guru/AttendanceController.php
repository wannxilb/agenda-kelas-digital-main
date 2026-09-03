<?php
// app/Http/Controllers/Guru/AttendanceController.php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Classes;
use App\Models\Attendance;
use App\Models\AttendanceLock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user();

        $academicYearId = session('academic_year_id')
            ?: \App\Models\AcademicYear::where('is_active', true)
                ->when($teacher->institution_id, fn ($q) => $q->where('institution_id', $teacher->institution_id))
                ->value('id');

        $classes = Classes::whereHas('schedules', function($q) use ($teacher, $academicYearId) {
            $q->where('teacher_id', $teacher->id);
            if ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            }
        })->get();

        $selectedClassId = $request->input('class_id');
        $students = collect();
        $isLocked = false;

        if ($selectedClassId) {
            if (!$classes->contains('id', $selectedClassId)) {
                $selectedClassId = null;
            }
        }

        if ($selectedClassId) {
            $isLocked = AttendanceLock::where('class_id', $selectedClassId)
                ->where('date', date('Y-m-d'))
                ->where('is_locked', true)
                ->exists();

            $students = User::role('siswa')
                ->where('class_id', $selectedClassId)
                ->with(['attendances' => function($q) {
                    $q->whereDate('date', date('Y-m-d'));
                }])
                ->orderByRaw('LOWER(name) ASC')
                ->get();
        }

        $resolvedStatuses = $students->isNotEmpty()
            ? app(\App\Services\AttendanceSummaryService::class)->resolveForDate($students->pluck('id')->all(), date('Y-m-d'))
            : [];

        return view('guru.attendance.index', compact('classes', 'students', 'selectedClassId', 'isLocked', 'resolvedStatuses'));
    }

    public function store(Request $request)
    {
        $teacher = Auth::user();

        $request->validate([
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institution_id', $teacher->institution_id)),
            ],
            'attendance' => 'required|array',
            'attendance.*.status' => 'nullable|in:present,absent,late,excused,sick',
            'attendance.*.note' => 'nullable|string|max:500',
            'date' => 'nullable|date|before_or_equal:today',
        ]);

        $classId = $request->class_id;

        $teachesClass = Classes::where('id', $classId)
            ->whereHas('schedules', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })->exists();

        if (!$teachesClass) {
            abort(403, 'Anda tidak mengajar di kelas ini.');
        }

        $isLocked = AttendanceLock::where('class_id', $classId)
            ->where('date', $request->input('date', date('Y-m-d')))
            ->where('is_locked', true)
            ->exists();

        if ($isLocked) {
            return redirect()->back()->with('error', 'Presensi untuk tanggal ini sudah dikunci.');
        }

        $attendances = $request->input('attendance', []);
        $date = $request->input('date', date('Y-m-d'));

        $validStudentIds = User::role('siswa')
            ->where('class_id', $classId)
            ->pluck('id')
            ->toArray();

        $filledCount = 0;

        foreach ($attendances as $studentId => $data) {
            if (!in_array($studentId, $validStudentIds)) {
                continue;
            }

            // Siswa yang masih "belum absen" (status tidak dipilih) dilewati
            if (empty($data['status'])) {
                continue;
            }

            $filledCount++;

            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'date' => $date,
                ],
                [
                    'class_id' => $classId,
                    'status' => $data['status'],
                    'note' => $data['note'] ?? null,
                    'check_in_time' => in_array($data['status'], ['present', 'late'], true) ? date('H:i:s') : null
                ]
            );
        }

        if ($filledCount === 0) {
            return redirect()->back()->with('warning', 'Tidak ada siswa yang diberi status. Pilih status untuk menyimpan presensi.');
        }

        return redirect()->route('guru.attendance.index', ['class_id' => $classId])
            ->with('success', 'Presensi berhasil disimpan!');
    }
}
