<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;
use Carbon\Carbon;

class CheckOperationalHours
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $institutionId = Auth::user()?->institution_id;
        $startTime = Setting::get('operational_start_time', '06:30', $institutionId);
        $endTime = Setting::get('operational_end_time', '16:00', $institutionId);
        $overrideUntil = Setting::get('operational_override_until', null, $institutionId);

        $operationalDaysStr = Setting::get('operational_days', '1,2,3,4,5', $institutionId); // Default: Senin sampai Jumat
        $operationalDays = explode(',', $operationalDaysStr);

        $now = Carbon::now();
        $start = Carbon::createFromTimeString($startTime);
        $end = Carbon::createFromTimeString($endTime);

        // Jika override masih berlaku (belum melewati batas waktu yang diset Admin)
        if ($overrideUntil && Carbon::parse($overrideUntil)->isFuture()) {
            return $next($request);
        }

        $currentDayOfWeek = (string) $now->dayOfWeekIso; // 1 (Senin) to 7 (Minggu)

        // Cek apakah hari ini termasuk hari operasional dan waktu saat ini berada di dalam jam operasional
        if (in_array($currentDayOfWeek, $operationalDays) && $now->between($start, $end)) {
            return $next($request);
        }

        // Jika request berupa AJAX / JSON (seperti update status presensi via fetch api dll)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Di luar hari/jam operasional. Harap minta izin Admin untuk membuka akses.'
            ], 403);
        }

        // Redirect dengan pesan error untuk form biasa
        return redirect()->back()->with('error', 'Akses ditutup! Kegiatan ini hanya dapat dilakukan pada hari/jam operasional yang ditentukan. Jika ada kesalahan input, harap hubungi Admin.');
    }
}
