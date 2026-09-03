<?php

use App\Models\Setting;
use Carbon\Carbon;

if (!function_exists('format_date')) {
    /**
     * Format a date string or Carbon instance using the global application date format.
     *
     * @param  \Carbon\Carbon|string|null  $date
     * @return string
     */
    function format_date($date)
    {
        if (!$date) return '-';

        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        $format = Setting::get('app_date_format', 'd/m/Y');
        
        // Convert JS format if necessary (e.g. DD/MM/YYYY to d/m/Y)
        $format = str_replace(['DD', 'MM', 'YYYY'], ['d', 'm', 'Y'], $format);

        return $date->format($format);
    }
}

if (!function_exists('format_time')) {
    /**
     * Format a time string or Carbon instance using the global application time format.
     *
     * @param  \Carbon\Carbon|string|null  $time
     * @return string
     */
    function format_time($time)
    {
        if (!$time) return '-';

        if (!$time instanceof Carbon) {
            $time = Carbon::parse($time);
        }

        $format = Setting::get('app_time_format', 'H:i');

        // Convert JS format if necessary (e.g. 24 Jam to H:i, 12 Jam to h:i A)
        if (str_contains($format, '24')) {
            $format = 'H:i';
        } elseif (str_contains($format, '12')) {
            $format = 'h:i A';
        }

        return $time->format($format);
    }
}
