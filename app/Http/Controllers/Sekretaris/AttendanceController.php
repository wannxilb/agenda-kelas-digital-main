<?php
// app/Http/Controllers/Sekretaris/AttendanceController.php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLock;
use App\Models\Classes;
use App\Models\User;
use App\Traits\ResolvesSekretarisClassContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    use ResolvesSekretarisClassContext;

    public function index(Request $request)
    {
        $context = $this->sekretarisClassContext($request);
        $classId = $context['selectedClassId'];

        if (!$classId) {
            return redirect()->route('sekretaris.dashboard')->with('error', 'Data kelas untuk periode yang dipilih tidak ditemukan.');
        }

        $class = $context['selectedClass'];
        if (!$class) {
            return redirect()->route('sekretaris.dashboard')->with('error', 'Data kelas Anda tidak ditemukan dalam sistem. Mohon hubungi Admin untuk memastikan pendaftaran kelas Anda sudah benar.');
        }

        if (!$context['isActivePeriod']) {
            return redirect()->route('sekretaris.attendance.report', ['class_id' => $classId]);
        }

        $classes = collect([$class]);
        $selectedClassId = $classId;
        $date = $request->date ?? date('Y-m-d');
        
        $students = $this->sekretarisStudentQuery($selectedClassId, $context['selectedAcademicYearId'], $context['isActivePeriod'])
            ->with(['attendances' => function($query) use ($date) {
                $query->withoutGlobalScope('academic_year');
                $query->where('date', $date);
            }])
            ->orderBy('name')
            ->get();

        $resolvedStatuses = app(\App\Services\AttendanceSummaryService::class)
            ->resolveForDate($students->pluck('id')->all(), $date);

        $isLocked = AttendanceLock::where('class_id', $selectedClassId)
            ->where('date', $date)
            ->where('is_locked', true)
            ->exists();
        
        return view('sekretaris.attendance.index', array_merge(
            compact('classes', 'students', 'selectedClassId', 'date', 'isLocked', 'resolvedStatuses'),
            $context
        ));
    }

    public function store(Request $request)
    {
        $context = $this->sekretarisClassContext($request);
        $classId = $context['selectedClassId'];
        if (!$classId) {
            return redirect()->back()->with('error', 'Anda belum terdaftar di kelas manapun.');
        }

        if (!$context['isActivePeriod']) {
            return redirect()->back()->with('error', 'Kelas yang dipilih hanya untuk melihat data (read only). Presensi tidak dapat disimpan.');
        }

        $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'attendance' => 'required|array',
            'attendance.*.status' => 'nullable|in:present,absent,late,excused,sick',
            'attendance.*.note' => 'nullable|string|max:255',
        ]);
        
        $date = \Carbon\Carbon::parse($request->date);
        $dayName = $date->format('l'); // Get full day name (e.g., Monday)
        
        // Verify schedule exists
        $hasSchedule = \App\Models\Schedule::where('class_id', $classId)
            ->where('day', $dayName)
            ->exists();
        
        if (!$hasSchedule) {
            return redirect()->back()->with('error', 'Tidak ada jadwal mengajar pada hari ' . $dayName . '. Presensi tidak dapat diisi.');
        }

        $dateFormatted = $date->format('Y-m-d');
        
        // Check if locked
        if (AttendanceLock::where('class_id', $classId)->where('date', $dateFormatted)->where('is_locked', true)->exists()) {
            return redirect()->back()->with('error', 'Presensi sudah dikunci dan tidak dapat diubah dari halaman ini.');
        }
        
        $validStudentIds = User::role('siswa')->where('class_id', $classId)->pluck('id')->toArray();
        
        $filledCount = 0;
        
        foreach ($request->attendance as $studentId => $data) {
            if (!in_array($studentId, $validStudentIds)) continue;

            // Siswa yang masih "belum absen" (status tidak dipilih) dilewati
            if (empty($data['status'])) continue;

            $filledCount++;

            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'date' => $dateFormatted,
                ],
                [
                    'class_id' => $classId,
                    'status' => $data['status'],
                    'note' => $data['note'] ?? null,
                    'check_in_time' => in_array($data['status'], ['present', 'late']) ? date('H:i:s') : null,
                ]
            );
        }

        if ($filledCount === 0) {
            return redirect()->back()->with('warning', 'Tidak ada siswa yang diberi status. Pilih status untuk menyimpan presensi.');
        }

        // Kunci hanya jika semua siswa sudah mendapatkan status
        if ($filledCount < count($validStudentIds)) {
            return redirect()->back()->with('warning', 'Masih ada ' . (count($validStudentIds) - $filledCount) . ' siswa yang belum absen. Perubahan tersimpan, tetapi presensi belum dikunci.');
        }

        // Lock attendance
        AttendanceLock::updateOrCreate(
            ['class_id' => $classId, 'date' => $dateFormatted],
            ['is_locked' => true]
        );

        return redirect()->back()->with('success', 'Presensi berhasil disimpan dan dikunci untuk tanggal ' . $dateFormatted);
    }

    
    public function report(Request $request)
    {
        $context = $this->sekretarisClassContext($request);
        $classId = $context['selectedClassId'];

        if (!$classId) {
            return redirect()->route('sekretaris.dashboard')->with('error', 'Data kelas untuk periode yang dipilih tidak ditemukan.');
        }

        $classes = collect([$context['selectedClass']]);
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $query = $this->sekretarisStudentQuery($classId, $context['selectedAcademicYearId'], $context['isActivePeriod']);

        $search = trim((string) $request->query('search'));
        if ($search !== '') {
            $search = mb_strtolower($search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(nis) LIKE ?', ["%{$search}%"]);
            });
        }
        
        $allStudents = (clone $query)->with(['class'])->orderBy('name', 'asc')->get();

        $students = $query->with(['class'])->orderBy('name', 'asc')->paginate(50)->withQueryString();

        $academicYearIds = $context['selectedAcademicYearId'] ? [$context['selectedAcademicYearId']] : [];
        $years = \App\Models\AcademicYear::whereIn('id', $academicYearIds)->get();
        $startDate = $startDate ?: null;
        $endDate = $endDate ?: null;
        $rangeStart = $startDate ?? $years->min('start_date')?->toDateString() ?? '1970-01-01';
        $rangeEnd = $endDate ?? $years->max('end_date')?->toDateString() ?? now()->toDateString();

        $resolved = app(\App\Services\AttendanceSummaryService::class)
            ->resolveForRange($allStudents->pluck('id')->all(), $rangeStart, $rangeEnd, $academicYearIds);
        $studentCounts = app(\App\Services\AttendanceSummaryService::class)->countPerStudent($resolved);
        $summary = app(\App\Services\AttendanceSummaryService::class)->countStatuses($resolved);

        $class = $context['selectedClass'];
        
        return view('sekretaris.attendance.report', array_merge(
            compact('classes', 'students', 'summary', 'class', 'studentCounts'),
            $context
        ));
    }

    public function studentAttendance(Request $request, int|string $id)
    {
        $context = $this->sekretarisClassContext($request);
        $classId = $context['selectedClassId'];

        if (!$classId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        $this->sekretarisStudentQuery($classId, $context['selectedAcademicYearId'], $context['isActivePeriod'])
            ->where('id', $id)
            ->firstOrFail();

        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $resolved = app(\App\Services\AttendanceSummaryService::class)
            ->resolveForRange([(int) $id], $startDate, $endDate);

        $notes = Attendance::withoutGlobalScope('academic_year')
            ->where('student_id', $id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn ($att) => $att->date->toDateString());

        $attendances = collect($resolved[(int) $id] ?? [])
            ->map(fn (string $status, string $date) => [
                'date' => $date,
                'status' => $status,
                'note' => $notes->get($date)?->note,
            ])
            ->sortByDesc('date')
            ->values();

        return response()->json(compact('attendances'));
    }
}
