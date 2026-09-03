<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\StudentDailyAttendance;
use App\Models\WhatsappNotificationLog;
use App\Traits\ResolvesSekretarisClassContext;
use Illuminate\Http\Request;

class DailyAttendanceController extends Controller
{
    use ResolvesSekretarisClassContext;

    public function index(Request $request)
    {
        $context = $this->sekretarisClassContext($request);
        $class = $context['selectedClass'];

        if (!$class) {
            return redirect()->route('sekretaris.dashboard')->with('error', 'Data kelas Anda tidak ditemukan.');
        }

        $date = $request->query('date', now()->toDateString());
        $records = StudentDailyAttendance::withoutGlobalScope('academic_year')
            ->where('class_id', $class->id)
            ->whereDate('date', $date)
            ->with(['student', 'class', 'whatsappLogs'])
            ->orderByRaw('check_in_at IS NULL, check_in_at ASC')
            ->get();
        $recordsByStudent = $records->keyBy('student_id');
        $absentWaLogsByStudent = WhatsappNotificationLog::whereIn('student_id', $class->students->pluck('id'))
            ->where('event_type', 'absent')
            ->whereDate('created_at', $date)
            ->latest()
            ->get()
            ->groupBy('student_id');
        $records = $class->students->map(function ($student) use ($recordsByStudent, $class, $date) {
            $record = $recordsByStudent->get($student->id);
            if ($record) return $record;

            $record = new StudentDailyAttendance([
                'student_id' => $student->id,
                'class_id' => $class->id,
                'date' => $date,
            ]);
            $record->setRelation('student', $student);
            $record->setRelation('class', $class);
            $record->setRelation('whatsappLogs', collect());
            return $record;
        });

        return view('daily-attendance.monitor', array_merge($context, [
            'layout' => 'layouts.sekretaris',
            'title' => 'Absensi Harian Siswa',
            'date' => $date,
            'records' => $records,
            'class' => $class,
            'showClass' => false,
            'absentWaLogsByStudent' => $absentWaLogsByStudent,
            'emptyText' => 'Belum ada absensi harian untuk kelas Anda pada tanggal ini.',
        ]));
    }
}
