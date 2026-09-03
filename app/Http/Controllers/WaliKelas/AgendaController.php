<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Traits\ResolvesWaliKelasContext;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    use ResolvesWaliKelasContext;

    public function index(Request $request)
    {
        $context = $this->waliKelasContext($request);
        $class = $context['selectedClass'];
        $academicYearId = $context['selectedAcademicYearId'];
        
        if (!$class) {
            return view('walikelas.agenda.index', array_merge($context, ['has_class' => false]));
        }

        $baseQuery = Agenda::withoutGlobalScope('academic_year')
            ->where('class_id', $class->id)
            ->where('status', 'published')
            ->when($academicYearId, fn ($query) => $query->where('academic_year_id', $academicYearId));

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'today' => (clone $baseQuery)->whereDate('date', today())->count(),
            'week' => (clone $baseQuery)->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'month' => (clone $baseQuery)->whereMonth('date', now()->month)->whereYear('date', now()->year)->count(),
        ];

        $query = (clone $baseQuery)->with(['teacher', 'subject']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%" . mb_strtolower($search) . "%"])
                    ->orWhereHas('subject', fn ($sq) => $sq->whereRaw('LOWER(name) LIKE ?', ["%" . mb_strtolower($search) . "%"]))
                    ->orWhereHas('teacher', fn ($tq) => $tq->whereRaw('LOWER(name) LIKE ?', ["%" . mb_strtolower($search) . "%"]));
            });
        }

        if ($request->filled('period')) {
            match ($request->period) {
                'today' => $query->whereDate('date', today()),
                'week' => $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]),
                'month' => $query->whereMonth('date', now()->month)->whereYear('date', now()->year),
                default => null,
            };
        }

        $agendas = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();
            
        return view('walikelas.agenda.index', array_merge($context, [
            'has_class' => true,
            'class' => $class,
            'agendas' => $agendas,
            'stats' => $stats,
        ]));
    }

    public function show(Request $request, int|string $agenda)
    {
        $context = $this->waliKelasContext($request);
        $allowedClassIds = $context['waliContexts']->pluck('class_id')->unique()->all();

        $agenda = Agenda::withoutGlobalScope('academic_year')
            ->with(['class', 'teacher', 'subject'])
            ->findOrFail($agenda);

        abort_unless(in_array($agenda->class_id, $allowedClassIds), 403);

        $agenda->load(['class', 'teacher', 'subject']);
        return view('walikelas.agenda.show', array_merge($context, compact('agenda')));
    }
}
