<?php

namespace App\Http\Controllers\Wakasek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Classes;
use App\Models\Subject;
use App\Models\Agenda;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CurriculumController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('view', User::class);

        $classQuery = Classes::withCount(['agendas' => function ($q) {
            $q->where('status', 'published');
        }]);
        $subjectQuery = Subject::withCount(['agendas' => function ($q) {
            $q->where('status', 'published');
        }]);
        
        if (request('q')) {
            $classQuery->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower(request('q')) . '%']);
            $subjectQuery->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower(request('q')) . '%']);
        }
        
        $classes = $classQuery->get();
        $subjects = $subjectQuery->get();
        
        return view('wakasek.curriculum.index', compact('classes', 'subjects'));
    }

    public function progress()
    {
        $this->authorize('view', User::class);

        // Detail progress per mapel per kelas
        $query = Agenda::where('status', 'published')
            ->select('class_id', 'subject_id', DB::raw('count(*) as total'))
            ->with(['class', 'subject']);
            
        if (request('q')) {
            $query->where(function ($query) {
                $query->whereHas('class', function($q) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower(request('q')) . '%']);
                })->orWhereHas('subject', function($q) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower(request('q')) . '%']);
                });
            });
        }
            
        $progress = $query->groupBy('class_id', 'subject_id')->get();
            
        return view('wakasek.curriculum.progress', compact('progress'));
    }
}
