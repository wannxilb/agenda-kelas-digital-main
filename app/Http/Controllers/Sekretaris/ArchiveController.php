<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $classIds = $user->classHistories()->pluck('class_id')->toArray();
        if ($user->class_id && !in_array($user->class_id, $classIds)) {
            $classIds[] = $user->class_id;
        }

        $query = Agenda::where('status', 'published')->whereIn('class_id', $classIds)
            ->with(['class', 'teacher', 'subject']);

        if ($request->filled('search')) {
            $query->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($request->search) . '%']);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $agendas = $query->orderBy('date', 'desc')->paginate(15);

        return view('sekretaris.agenda.archive', compact('agendas'));
    }
}
