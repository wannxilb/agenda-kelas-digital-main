<?php

namespace App\Traits;

use App\Models\AcademicYear;

trait HasAcademicYear
{
    public static function bootHasAcademicYear()
    {
        static::creating(function ($model) {
            if (empty($model->academic_year_id)) {
                $sessionYearId = request('academic_year_id') ?: session('academic_year_id');
                $sessionYearId = is_array($sessionYearId)
                    ? (implode(',', $sessionYearId) ?: null)
                    : $sessionYearId;
                
                if ($sessionYearId) {
                    // Try to get IDs if it's a comma-separated string (unlikely for creating, but just in case)
                    $id = is_string($sessionYearId) && str_contains($sessionYearId, ',') 
                        ? explode(',', $sessionYearId)[0] 
                        : $sessionYearId;
                    $model->academic_year_id = $id;
                } else {
                    $activeYearQuery = AcademicYear::where('is_active', true);
                    if (auth()->check() && auth()->user()->institution_id) {
                        $activeYearQuery->where('institution_id', auth()->user()->institution_id);
                    }
                    $activeYear = $activeYearQuery->first();
                    if ($activeYear) {
                        $model->academic_year_id = $activeYear->id;
                    }
                }
            }
        });

        static::addGlobalScope('academic_year', function (\Illuminate\Database\Eloquent\Builder $builder) {
            // Normalisasi ke string: hindari explode() pada array saat parameter
            // academic_year_id dikirim sebagai array (mis. dari filter report).
            $selectedAcademicYear = request('academic_year_id');
            $selectedAcademicYear = is_array($selectedAcademicYear)
                ? implode(',', $selectedAcademicYear)
                : $selectedAcademicYear;

            if (
                (!$selectedAcademicYear || $selectedAcademicYear === '')
                && request()->isMethod('GET')
                && !request()->routeIs('*.create')
                && !request()->routeIs('*.edit')
                && session()->has('academic_year_id')
            ) {
                $selectedAcademicYear = session('academic_year_id');
                $selectedAcademicYear = is_array($selectedAcademicYear)
                    ? implode(',', $selectedAcademicYear)
                    : $selectedAcademicYear;
            }

            if ($selectedAcademicYear && $selectedAcademicYear != '') {
                $ids = collect(explode(',', $selectedAcademicYear))
                    ->map(fn ($id) => trim($id))
                    ->filter(fn ($id) => ctype_digit((string) $id))
                    ->values()
                    ->all();

                if (empty($ids)) {
                    $ids = AcademicYear::whereIn('name', collect(explode(',', $selectedAcademicYear))
                        ->map(fn ($name) => trim($name))
                        ->filter()
                        ->all())
                        ->when(auth()->check() && auth()->user()->institution_id, function($q) {
                            $q->where('institution_id', auth()->user()->institution_id);
                        })
                        ->pluck('id')
                        ->all();
                }

                if (!empty($ids)) {
                    $builder->whereIn('academic_year_id', $ids);
                } else {
                    $builder->whereRaw('1 = 0');
                }
            } else {
                $activeYearQuery = AcademicYear::where('is_active', true);
                if (auth()->check() && auth()->user()->institution_id) {
                    $activeYearQuery->where('institution_id', auth()->user()->institution_id);
                }
                $activeYear = $activeYearQuery->first();
                
                if ($activeYear) {
                    $builder->where('academic_year_id', $activeYear->id);
                }
            }
        });
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
