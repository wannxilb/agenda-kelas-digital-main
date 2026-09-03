<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSystemMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $enabled = Setting::get('maintenance_enabled', '0');
        $message = Setting::get('maintenance_message', 'Sistem sedang dalam pemeliharaan.');

        if ($enabled === '1') {
            // Allow Super Admins to bypass maintenance
            /** @var User|null $user */
            $user = Auth::user();
            if ($user && $user->hasRole('super_admin')) {
                return $next($request);
            }

            // For AJAX requests, return a JSON response
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 503);
            }

            // Redirect to a maintenance page or show a simple response
            // For now, we'll just redirect to login with the message
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', $message);
        }

        return $next($request);
    }
}
