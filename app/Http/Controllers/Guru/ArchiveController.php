<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user();
        $academicYearId = $this->resolveAcademicYearId($request);

        $query = Agenda::withoutGlobalScope('academic_year')
            ->where('teacher_id', $teacher->id)
            ->where('status', 'published')
            ->with(['class', 'subject', 'teacher']);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        
        if ($request->has('search') && $request->search) {
            $query->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($request->search) . '%']);
        }
        
        if ($request->has('class_id') && $request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        
        $agendas = $query->orderBy('date', 'desc')->paginate(15);
        
        // Ambil daftar kelas yang diajar oleh guru ini (berdasarkan jadwal)
        $classes = \App\Models\Classes::whereHas('schedules', function($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })->get();
        
        return view('guru.agenda.archive', compact('agendas', 'classes'));
    }

    private function resolveAcademicYearId(Request $request): ?int
    {
        $teacher = Auth::user();
        $institutionId = $teacher?->institution_id;
        $academicYearId = $request->query('academic_year_id') ?: $request->session()->get('academic_year_id');

        if ($academicYearId && ctype_digit((string) $academicYearId)) {
            $exists = AcademicYear::query()
                ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
                ->whereKey((int) $academicYearId)
                ->exists();

            if ($exists) {
                return (int) $academicYearId;
            }
        }

        return AcademicYear::query()
            ->when($institutionId, fn ($q) => $q->where('institution_id', $institutionId))
            ->where('is_active', true)
            ->value('id');
    }
}
