<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureAccess
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (!\App\Services\FeatureService::isEnabled($feature)) {
            abort(403, __('Fitur ini sedang dinonaktifkan oleh administrator sistem.'));
        }

        return $next($request);
    }
}
