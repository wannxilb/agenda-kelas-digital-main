<?php

namespace App\Traits;

use App\Models\Classes;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait ResolvesSekretarisClassContext
{
    protected function sekretarisClassContext(Request $request): array
    {
        $user = Auth::user();
        $currentClass = $user->class_id ? Classes::find($user->class_id) : null;
        $availableClasses = $user->classHistories()
            ->with('class')
            ->get()
            ->pluck('class')
            ->filter()
            ->when($currentClass, fn ($classes) => $classes->push($currentClass))
            ->filter()
            ->unique('id')
            ->sortBy([['grade_level', 'asc'], ['name', 'asc']])
            ->values();

        $requestedClassId = $request->query('class_id');
        $selectedClass = $requestedClassId
            ? $availableClasses->firstWhere('id', (int) $requestedClassId)
            : null;

        if (!$selectedClass) {
            $selectedClass = $currentClass ?: $availableClasses->first();
        }

        $isActivePeriod = $currentClass && $selectedClass && (int) $selectedClass->id === (int) $currentClass->id;

        return [
            'availableClasses' => $availableClasses,
            'selectedClass' => $selectedClass,
            'selectedClassId' => optional($selectedClass)->id,
            'selectedAcademicYearId' => null,
            'isActivePeriod' => $isActivePeriod,
        ];
    }

    protected function sekretarisStudentQuery(int $classId, ?int $academicYearId = null, bool $isActivePeriod = false): Builder
    {
        return User::role('siswa')
            ->when($isActivePeriod, function ($query) use ($classId) {
                $query->where('class_id', $classId)->where('status', '!=', 'graduated');
            }, function ($query) use ($classId) {
                $query->where(function ($q) use ($classId) {
                    $q->where('class_id', $classId)
                        ->orWhereHas('classHistories', fn ($history) => $history->where('class_id', $classId));
                });
            });
    }
}
