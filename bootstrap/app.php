<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        // Purge old audit logs daily at midnight
        $schedule->command('audit:purge')->daily();
        $schedule->command('attendance:purge-media')->dailyAt('00:30');
        
        // Send agenda reminders every morning at 06:00
        $schedule->command('agenda:reminder')->dailyAt('06:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\SetSystemLocale::class,
            \App\Http\Middleware\ShareAcademicPeriod::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
        
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'operational.hours' => \App\Http\Middleware\CheckOperationalHours::class,
            'agenda.location' => \App\Http\Middleware\CheckAgendaLocation::class,
            'system.maintenance' => \App\Http\Middleware\CheckSystemMaintenance::class,
            'feature' => \App\Http\Middleware\CheckFeatureAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (
                $exception->getStatusCode() === 403
                && $request->isMethod('post')
                && $request->is('siswa/daily-attendance/*')
            ) {
                $message = $exception->getMessage();
                if (
                    !$message
                    || str_contains($message, 'User does not')
                    || str_contains($message, 'User is not logged in')
                ) {
                    $message = 'Akses absensi ditolak. Muat ulang halaman, pastikan login sebagai siswa, lalu coba lagi.';
                }

                return redirect()->back()->withInput()->with('error', $message);
            }

            return null;
        });
    })->create();
