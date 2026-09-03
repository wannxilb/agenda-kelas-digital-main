<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class SetSystemLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            if (Schema::hasTable('settings')) {
                $locale = Setting::get('app_language', config('app.locale'));
                App::setLocale($locale);
            }
        } catch (\Exception $e) {
            // Ignore if DB is not ready or settings table doesn't exist yet
        }
        
        return $next($request);
    }
}
