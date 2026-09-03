<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Policies\WakasekPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/DateHelper.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        // Rate limiting definitions. Nilai moderate: cukup untuk polling halaman
        // (30s => ~2x/menit, 15s => ~4x/menit) sekaligus mencegah serangan berlebihan.
        // Output tinggi seperti export/PDF dibuat ketat karena mahal.
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('polls', function ($request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // IP-based, murah untuk endpoint yang tidak berdampak query berat.
        RateLimiter::for('guest', function ($request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // Ketat untuk export/report berat.
        RateLimiter::for('heavy', function ($request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Implicitly grant "Super Admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        // Register Policies
        Gate::policy(User::class, WakasekPolicy::class);

        // Load dynamic settings from database
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                // 1. General Settings
                $appName = \App\Models\Setting::get('app_name');
                if ($appName) {
                    config(['app.name' => $appName]);
                }

                $timezone = \App\Models\Setting::get('app_timezone');
                if ($timezone) {
                    config(['app.timezone' => $timezone]);
                    date_default_timezone_set($timezone);
                }

                $language = \App\Models\Setting::get('app_language');
                if ($language) {
                    \Illuminate\Support\Facades\App::setLocale($language);
                    config(['app.locale' => $language]);
                }

                // 2. Email / SMTP Settings
                $mailHost = \App\Models\Setting::get('mail_host');
                if ($mailHost) {
                    config([
                        'mail.mailers.smtp.host'       => $mailHost,
                        'mail.mailers.smtp.port'       => \App\Models\Setting::get('mail_port', config('mail.mailers.smtp.port')),
                        'mail.mailers.smtp.username'   => \App\Models\Setting::get('mail_username', config('mail.mailers.smtp.username')),
                        'mail.mailers.smtp.password'   => \App\Models\Setting::get('mail_password', config('mail.mailers.smtp.password')),
                        'mail.mailers.smtp.encryption' => \App\Models\Setting::get('mail_encryption', config('mail.mailers.smtp.encryption')),
                        'mail.from.address'            => \App\Models\Setting::get('mail_from_address', config('mail.from.address')),
                        'mail.from.name'               => $appName ?? config('mail.from.name'),
                    ]);
                }

                // 3. Auth Settings — key in DB is 'auth_session_timeout' (minutes)
                $autoLogout = \App\Models\Setting::get('auth_session_timeout');
                if ($autoLogout && (int) $autoLogout > 0) {
                    config(['session.lifetime' => (int) $autoLogout]);
                }
            }
        } catch (\Exception $e) {
            // Ignore during setup/migrations
        }

        // Register custom Blade directives
        \Illuminate\Support\Facades\Blade::if('feature', function ($feature) {
            try {
                return \App\Services\FeatureService::isEnabled($feature);
            } catch (\Exception $e) {
                return true; // Default to true if DB not ready
            }
        });
    }
}
