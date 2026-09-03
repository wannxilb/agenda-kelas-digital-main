<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log($action, $tableName, $recordId, $institutionId = null, $oldValues = null, $newValues = null)
    {
        $actionLower = strtolower($action);
        
        // Determine log type based on action string
        $isLoginLogout = str_contains($actionLower, 'login') || str_contains($actionLower, 'logout');
        $isError = str_contains($actionLower, 'error') || str_contains($actionLower, 'exception') || str_contains($actionLower, 'fail');
        // If it's not login/logout and not error, we consider it CRUD/general activity
        $isCrud = !$isLoginLogout && !$isError;

        // Check settings (defaults are '1' if not set)
        if ($isLoginLogout && \App\Models\Setting::get('audit_log_login', '1') !== '1') {
            return null;
        }
        
        if ($isError && \App\Models\Setting::get('audit_log_error', '1') !== '1') {
            return null;
        }

        if ($isCrud && \App\Models\Setting::get('audit_log_crud', '1') !== '1') {
            return null;
        }

        return AuditLog::create([
            'user_id' => Auth::id(),
            'institution_id' => $institutionId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'browser' => request()->userAgent(),
        ]);
    }
}
