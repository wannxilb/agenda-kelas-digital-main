<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Models\TeacherStatus;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAgendaLocation
{
    public function handle(Request $request, Closure $next): Response
    {
        $institutionId = $request->user()?->institution_id;

        $locationEnabled = Setting::get('school_location_enabled', Setting::get('agenda_location_enabled', '0', $institutionId), $institutionId);
        if ($locationEnabled !== '1') {
            return $next($request);
        }

        $centerLat = Setting::get('school_location_latitude', Setting::get('agenda_location_latitude', null, $institutionId), $institutionId);
        $centerLng = Setting::get('school_location_longitude', Setting::get('agenda_location_longitude', null, $institutionId), $institutionId);
        $radius = (float) Setting::get('school_location_radius_meters', Setting::get('agenda_location_radius_meters', 100, $institutionId), $institutionId);

        if (! is_numeric($centerLat) || ! is_numeric($centerLng) || $radius <= 0) {
            return $this->deny($request, 'Wilayah pengisian agenda belum dikonfigurasi oleh Admin.');
        }

        $userLat = $request->input('agenda_latitude');
        $userLng = $request->input('agenda_longitude');

        if (! is_numeric($userLat) || ! is_numeric($userLng)) {
            return $this->deny($request, 'Lokasi wajib diaktifkan untuk mengisi agenda.');
        }

        $distance = $this->distanceInMeters((float) $centerLat, (float) $centerLng, (float) $userLat, (float) $userLng);

        if ($distance > $radius && ! $this->hasApprovedRemoteTeacherStatus($request)) {
            return $this->deny($request, 'Agenda hanya dapat diisi dari wilayah yang sudah ditentukan Admin.');
        }

        return $next($request);
    }

    private function hasApprovedRemoteTeacherStatus(Request $request): bool
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'hasRole') || ! $user->hasRole('teacher')) {
            return false;
        }

        try {
            $date = Carbon::parse($request->input('date', today()))->toDateString();
        } catch (\Throwable) {
            return false;
        }

        return TeacherStatus::query()
            ->where('teacher_id', $user->id)
            ->where('status', 'approved')
            ->whereIn('type', ['izin', 'sakit', 'tugas_luar'])
            ->where('date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('date_end')->where('date', '>=', $date)
                    ->orWhere(function ($q) use ($date) {
                        $q->whereNotNull('date_end')->where('date_end', '>=', $date);
                    });
            })
            ->exists();
    }

    private function deny(Request $request, string $message): Response
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], 403);
        }

        return redirect()->back()->with('error', $message)->withInput();
    }

    private function distanceInMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
