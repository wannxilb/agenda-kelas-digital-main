<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Subject;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->class_id) {
            return view('siswa.agenda.index', [
                'agendas' => new LengthAwarePaginator(collect(), 0, 10),
                'subjects' => collect(),
                'groupedAgendas' => collect(),
                'todayStr' => Carbon::today()->toDateString(),
                'yesterdayStr' => Carbon::yesterday()->toDateString(),
                'noClass' => true,
            ]);
        }

        $academicYearId = $request->query('academic_year_id')
            ?: session('academic_year_id')
            ?: AcademicYear::where('is_active', true)->first()?->id;

        $query = Agenda::withoutGlobalScope('academic_year')
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->where('class_id', $user->class_id)
            ->where('status', 'published')
            ->with(['teacher', 'subject', 'class']);

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($search) . '%']);
        }

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $agendas = $query->orderBy('date', 'desc')->paginate(10);

        $subjects = Subject::whereHas('schedules', function ($q) use ($user) {
            $q->where('class_id', $user->class_id);
        })->get();

        $todayStr = Carbon::today()->toDateString();
        $yesterdayStr = Carbon::yesterday()->toDateString();

        $agendaDates = Agenda::withoutGlobalScope('academic_year')
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->where('class_id', $user->class_id)
            ->where('status', 'published')
            ->where('date', '>=', Carbon::now()->subMonths(3)->startOfMonth()->toDateString())
            ->where('date', '<=', Carbon::now()->endOfMonth()->toDateString())
            ->pluck('date')
            ->map(function ($date) {
                return $date instanceof Carbon ? $date->toDateString() : (string) $date;
            })
            ->unique()
            ->values();

        $groupedAgendas = [];
        foreach ($agendas as $agenda) {
            $dateKey = $agenda->date instanceof \Carbon\Carbon
                ? $agenda->date->toDateString()
                : (string) $agenda->date;

            if (!isset($groupedAgendas[$dateKey])) {
                $groupedAgendas[$dateKey] = [];
            }
            $groupedAgendas[$dateKey][] = $agenda;
        }

        return view('siswa.agenda.index', compact('agendas', 'subjects', 'groupedAgendas', 'todayStr', 'yesterdayStr', 'agendaDates'));
    }

    public function showJson(int|string $id)
    {
        $user = Auth::user();

        $agenda = Agenda::where('class_id', $user->class_id)
            ->where('status', 'published')
            ->with(['teacher', 'subject', 'class'])
            ->findOrFail($id);

        $attachmentUrl = null;
        if ($agenda->attachments) {
            $path = is_array($agenda->attachments)
                ? ($agenda->attachments[0] ?? null)
                : $agenda->attachments;

            $attachmentUrl = $path ? url('storage/' . $path) : null;
        }

        return response()->json([
            'title'        => $agenda->title,
            'subject_name' => $agenda->subject->name ?? 'Umum',
            'date'         => Carbon::parse($agenda->date)->translatedFormat('d F Y'),
            'teacher_name' => $agenda->teacher->name,
            'class_name'   => $agenda->class->name,
            'room'         => $agenda->room,
            'description'  => strip_tags($agenda->description),
            'attachments'  => $attachmentUrl,
        ]);
    }
}
