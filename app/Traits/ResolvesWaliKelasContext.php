<?php

namespace App\Traits;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\ClassHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait ResolvesWaliKelasContext
{
    protected static $cachedActiveAcademicYear = false;

    protected function waliKelasContext(Request $request): array
    {
        $teacher = Auth::user();
        $activeClasses = Classes::where('homeroom_teacher_id', $teacher->id)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $contexts = collect();

        foreach ($activeClasses as $class) {
            $contexts->push([
                'key' => 'active:' . $class->id,
                'label' => 'Kelas aktif - ' . $class->name,
                'class' => $class,
                'class_id' => $class->id,
                'academic_year' => $this->activeAcademicYear(),
                'academic_year_id' => $this->activeAcademicYear()?->id,
                'is_active_context' => true,
            ]);
        }

        ClassHistory::where('homeroom_teacher_id', $teacher->id)
            ->with(['class', 'academicYear'])
            ->get()
            ->filter(fn ($history) => $history->class && $history->academicYear)
            ->unique(fn ($history) => $history->class_id . ':' . $history->academic_year_id)
            ->sortByDesc(fn ($history) => optional($history->academicYear?->start_date)->timestamp ?? $history->academic_year_id)
            ->each(function ($history) use ($contexts, $activeClasses) {
                $isAlsoActive = $activeClasses->contains('id', $history->class_id)
                    && $history->academic_year_id === $this->activeAcademicYear()?->id;

                if ($isAlsoActive) {
                    return;
                }

                $contexts->push([
                    'key' => 'history:' . $history->academic_year_id . ':' . $history->class_id,
                    'label' => ($history->academicYear->name ?? 'Riwayat') . ' - ' . ($history->academicYear->semester ?? '-') . ' - ' . $history->class->name,
                    'class' => $history->class,
                    'class_id' => $history->class_id,
                    'academic_year' => $history->academicYear,
                    'academic_year_id' => $history->academic_year_id,
                    'is_active_context' => false,
                ]);
            });

        $selectedKey = $request->query('wali_context');
        $selected = $selectedKey
            ? $contexts->firstWhere('key', $selectedKey)
            : null;

        if (!$selected) {
            $selected = $contexts->firstWhere('is_active_context', true) ?: $contexts->first();
        }

        return [
            'waliContexts' => $contexts->values(),
            'selectedWaliContext' => $selected,
            'selectedWaliContextKey' => $selected['key'] ?? null,
            'selectedClass' => $selected['class'] ?? null,
            'selectedClassId' => $selected['class_id'] ?? null,
            'selectedAcademicYear' => $selected['academic_year'] ?? null,
            'selectedAcademicYearId' => $selected['academic_year_id'] ?? null,
            'isActiveWaliContext' => (bool) ($selected['is_active_context'] ?? false),
        ];
    }

    protected function waliKelasStudentsQuery(int $classId, ?int $academicYearId, bool $isActiveContext): Builder
    {
        return User::role('siswa')
            ->when($isActiveContext, function ($query) use ($classId) {
                $query->where('class_id', $classId)->where('status', '!=', 'graduated');
            }, function ($query) use ($classId, $academicYearId) {
                $query->whereHas('classHistories', function ($history) use ($classId, $academicYearId) {
                    $history->where('class_id', $classId);

                    if ($academicYearId) {
                        $history->where('academic_year_id', $academicYearId);
                    }
                });
            });
    }

    protected function activeAcademicYear(): ?AcademicYear
    {
        if (static::$cachedActiveAcademicYear === false) {
            $institutionId = Auth::user()?->institution_id;
            $query = AcademicYear::where('is_active', true);
            if ($institutionId) {
                $query->where('institution_id', $institutionId);
            }
            static::$cachedActiveAcademicYear = $query->first();
        }

        return static::$cachedActiveAcademicYear ?: null;
    }
}
