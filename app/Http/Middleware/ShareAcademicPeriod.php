<?php

namespace App\Http\Middleware;

use App\Models\AcademicYear;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareAcademicPeriod
{
    public function handle(Request $request, Closure $next): Response
    {
        $academicPeriods = collect();
        $activeAcademicPeriod = null;
        $selectedAcademicPeriod = null;

        if (Auth::check()) {
            /** @var User|null $user */
            $user = Auth::user();

            $academicPeriods = AcademicYear::query()
                ->when($user?->institution_id, fn ($q) => $q->where('institution_id', $user->institution_id))
                ->orderBy('name', 'desc')
                ->orderByRaw("CASE WHEN semester = 'Ganjil' THEN 0 ELSE 1 END")
                ->get();
            $activeAcademicPeriod = $academicPeriods->firstWhere('is_active', true);

            $usesGlobalAcademicPeriod = !$user?->hasRole('wakasek') || !$request->routeIs('wakasek.*');
            $selectedId = $request->query('academic_year_id')
                ?: ($usesGlobalAcademicPeriod ? $request->session()->get('academic_year_id') : null);
            $selectedAcademicPeriod = $selectedId
                ? $academicPeriods->firstWhere('id', (int) $selectedId)
                : $activeAcademicPeriod;

            if (!$selectedAcademicPeriod) {
                $selectedAcademicPeriod = $activeAcademicPeriod ?: $academicPeriods->first();
            }

            $isWriteScreen = $request->routeIs('*.create') || $request->routeIs('*.edit');

            if (
                $request->isMethod('GET')
                && $usesGlobalAcademicPeriod
                && !$isWriteScreen
                && !$request->ajax()
                && $request->session()->has('academic_year_id')
                && $selectedAcademicPeriod
                && (string) $request->query('academic_year_id') !== (string) $selectedAcademicPeriod->id
            ) {
                return redirect($request->fullUrlWithQuery(['academic_year_id' => $selectedAcademicPeriod->id]));
            }
        }

        View::share([
            'academicPeriods' => $academicPeriods,
            'activeAcademicPeriod' => $activeAcademicPeriod,
            'selectedAcademicPeriod' => $selectedAcademicPeriod,
            'isArchivePeriod' => $activeAcademicPeriod
                && $selectedAcademicPeriod
                && $activeAcademicPeriod->id !== $selectedAcademicPeriod->id,
        ]);

        return $next($request);
    }
}
