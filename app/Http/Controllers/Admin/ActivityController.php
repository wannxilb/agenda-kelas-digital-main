<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Agenda::where('status', 'published')
            ->with(['class', 'teacher'])
            ->latest()
            ->paginate(20);

        return view('admin.activities.index', compact('activities'));
    }
}
