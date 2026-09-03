<?php
// app/Http/Controllers/Sekretaris/PrintController.php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Agenda;
use App\Models\Classes;
use App\Traits\ResolvesSekretarisClassContext;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PrintController extends Controller
{
    use ResolvesSekretarisClassContext;

    public function printAttendance(Request $request)
    {
        $context = $this->sekretarisClassContext($request);
        $classId = $context['selectedClassId'];

        if (!$classId) {
            abort(403, 'Data kelas untuk periode yang dipilih tidak ditemukan.');
        }

        $query = Attendance::withoutGlobalScope('academic_year')->where('class_id', $classId)->with(['student.class']);
        
        if ($request->start_date) {
            $query->where('date', '>=', $request->start_date);
        }
        
        if ($request->end_date) {
            $query->where('date', '<=', $request->end_date);
        }
        
        $attendances = $query->latest('date')->get();
        $class = $context['selectedClass'];
        if (!$class) {
            abort(404, 'Kelas tidak ditemukan.');
        }
        
        $pdf = Pdf::loadView('admin.reports.attendance-pdf', compact('attendances', 'class'));
        return $pdf->stream('laporan-presensi-' . $class->name . '-' . date('Y-m-d') . '.pdf');
    }

    public function printAgenda(Request $request)
    {
        $context = $this->sekretarisClassContext($request);
        $classId = $context['selectedClassId'];

        if (!$classId) {
            abort(403, 'Data kelas untuk periode yang dipilih tidak ditemukan.');
        }

        $query = Agenda::withoutGlobalScope('academic_year')->where('class_id', $classId)->where('status', 'published')->with(['class', 'teacher', 'subject']);
        
        if ($request->date) {
            $query->where('date', $request->date);
        }
        
        $agendas = $query->latest('date')->get();
        $class = $context['selectedClass'];
        if (!$class) {
            abort(404, 'Kelas tidak ditemukan.');
        }

        $pdf = Pdf::loadView('sekretaris.agenda.print-pdf', [
            'agendas' => $agendas,
            'class' => $class,
        ]);
        
        return $pdf->stream('laporan-agenda-' . $class->name . '-' . date('Y-m-d') . '.pdf');
    }
}
