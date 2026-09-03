<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AcademicPeriodController extends Controller
{
    public function switch(Request $request)
    {
        $institutionId = Auth::user()?->institution_id;
        $academicYearRule = Rule::exists('academic_years', 'id');
        if ($institutionId) {
            $academicYearRule->where(fn ($q) => $q->where('institution_id', $institutionId));
        }

        $validated = $request->validate([
            'academic_year_id' => [
                'required',
                'integer',
                $academicYearRule,
            ],
        ]);

        $academicYear = AcademicYear::findOrFail($validated['academic_year_id']);
        $request->session()->put('academic_year_id', $academicYear->id);

        return redirect($this->previousWithPeriodQuery($academicYear->id))
            ->with('success', 'Periode data diganti ke ' . $academicYear->name . ' - Semester ' . $academicYear->semester . '.');
    }

    public function reset(Request $request)
    {
        $request->session()->forget('academic_year_id');

        return redirect($this->previousWithoutPeriodQuery())
            ->with('success', 'Periode data dikembalikan ke semester aktif.');
    }

    private function previousWithoutPeriodQuery(): string
    {
        $previous = url()->previous();
        $parts = parse_url($previous);
        $query = [];

        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        unset($query['academic_year_id'], $query['academic_year_name']);

        $target = $parts['path'] ?? '/';
        if (!empty($query)) {
            $target .= '?' . http_build_query($query);
        }
        if (!empty($parts['fragment'])) {
            $target .= '#' . $parts['fragment'];
        }

        return $target;
    }

    private function previousWithPeriodQuery(int $academicYearId): string
    {
        $previous = url()->previous();
        $parts = parse_url($previous);
        $query = [];

        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $query['academic_year_id'] = $academicYearId;
        unset($query['academic_year_name']);

        $target = $parts['path'] ?? '/';
        if (!empty($query)) {
            $target .= '?' . http_build_query($query);
        }
        if (!empty($parts['fragment'])) {
            $target .= '#' . $parts['fragment'];
        }

        return $target;
    }
}
