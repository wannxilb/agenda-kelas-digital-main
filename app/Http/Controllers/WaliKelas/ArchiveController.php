<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Classes;
use App\Models\ClassHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user();

        // Ambil semua kelas aktif dan historis yang pernah dibina wali kelas ini.
        $classIds = collect(Classes::where('homeroom_teacher_id', $teacher->id)->pluck('id'))
            ->merge(ClassHistory::where('homeroom_teacher_id', $teacher->id)->pluck('class_id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($classIds)) {
            return view('walikelas.agenda.archive', [
                'agendas' => collect()->paginate(15),
                'classes' => collect(),
            ]);
        }

        $query = Agenda::withoutGlobalScope('academic_year')
            ->where('status', 'published')
            ->whereIn('class_id', $classIds)
            ->with(['class', 'teacher', 'subject']);

        if ($request->has('search') && $request->search) {
            $query->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($request->search) . '%']);
        }

        if ($request->has('class_id') && $request->class_id) {
            $query->where('class_id', $request->class_id);
        }

        $agendas = $query->orderBy('date', 'desc')->paginate(15);

        $classes = Classes::whereIn('id', $classIds)->get();

        return view('walikelas.agenda.archive', compact('agendas', 'classes'));
    }
}
