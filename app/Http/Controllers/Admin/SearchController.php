<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Agenda;
use App\Models\AcademicYear;
use App\Models\Room;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        
        if (!$query) {
            return redirect()->back();
        }

        // Search Students
        $students = User::role('siswa')
            ->where(function($q) use ($query) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%" . mb_strtolower($query) . "%"])
                  ->orWhereRaw('LOWER(nis) LIKE ?', ["%" . mb_strtolower($query) . "%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%" . mb_strtolower($query) . "%"])
                  ->orWhereHas('class', function($subQuery) use ($query) {
                      $subQuery->whereRaw('LOWER(name) LIKE ?', ["%" . mb_strtolower($query) . "%"]);
                  });
            })
            ->with('class')
            ->take(5)
            ->get();

        // Search Teachers
        $teachers = User::role('teacher')
            ->where(function($q) use ($query) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%" . mb_strtolower($query) . "%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%" . mb_strtolower($query) . "%"]);
            })
            ->take(5)
            ->get();

        // Search Classes
        $classes = Classes::whereRaw('LOWER(name) LIKE ?', ["%" . mb_strtolower($query) . "%"])
            ->with('homeroomTeacher')
            ->take(5)
            ->get();

        // Search Subjects
        $subjects = Subject::whereRaw('LOWER(name) LIKE ?', ["%" . mb_strtolower($query) . "%"])
            ->take(5)
            ->get();

        // Search Agendas
        $agendas = Agenda::where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%" . mb_strtolower($query) . "%"])
                  ->orWhereRaw('LOWER(description) LIKE ?', ["%" . mb_strtolower($query) . "%"]);
            })
            ->with(['class', 'teacher'])
            ->latest()
            ->take(5)
            ->get();

        // Search Academic Years
        $academicYears = AcademicYear::whereRaw('LOWER(name) LIKE ?', ["%" . mb_strtolower($query) . "%"])
            ->take(5)
            ->get();

        // Search Rooms
        $rooms = Room::whereRaw('LOWER(name) LIKE ?', ["%" . mb_strtolower($query) . "%"])
            ->take(5)
            ->get();

        $totalResults = $students->count() + $teachers->count() + $classes->count() + $subjects->count() + $agendas->count() + $academicYears->count() + $rooms->count();

        return view('admin.search_results', [
            'query' => $query,
            'resultStudents' => $students,
            'resultTeachers' => $teachers,
            'resultClasses' => $classes,
            'resultSubjects' => $subjects,
            'resultAgendas' => $agendas,
            'resultAcademicYears' => $academicYears,
            'resultRooms' => $rooms,
            'totalResults' => $totalResults
        ]);
    }
}
