<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\ClassHistory;
use Illuminate\Http\Request;

class ClassHistoryController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::select('name')->distinct()->orderBy('name', 'desc')->get();
        
        $activeYear = AcademicYear::where('is_active', true)->first();
        $selectedYearName = $request->academic_year_name ?? ($activeYear ? $activeYear->name : ($academicYears->first()->name ?? null));
        $searchGrade = $request->grade_level;
        
        // Ambil daftar kelas yang memiliki riwayat pada tahun ajaran tersebut
        $query = Classes::whereHas('classHistories.academicYear', function($q) use ($selectedYearName) {
            $q->where('name', $selectedYearName);
        });

        // Filter berdasarkan tingkatan kelas
        if ($searchGrade) {
            $query->where('grade_level', $searchGrade);
        }

        $classes = $query->with(['classHistories' => function($q) use ($selectedYearName) {
            $q->whereHas('academicYear', function($y) use ($selectedYearName) {
                $y->where('name', $selectedYearName);
            })->with('homeroomTeacher');
        }])->orderBy('name')->get();

        return view('admin.academic_records.index', compact('classes', 'academicYears', 'selectedYearName', 'searchGrade'));
    }

    public function show(Request $request, int|string $classId, string $yearName)
    {
        $class = Classes::findOrFail($classId);
        $histories = ClassHistory::with(['user', 'homeroomTeacher'])
            ->where('class_id', $classId)
            ->whereHas('academicYear', function($q) use ($yearName) {
                $q->where('name', $yearName);
            })
            ->get()
            ->sort(function ($a, $b) {
                return strnatcasecmp($a->user->name, $b->user->name);
            })
            ->values();
            
        return view('admin.academic_records.show', compact('class', 'histories', 'yearName'));
    }
}
