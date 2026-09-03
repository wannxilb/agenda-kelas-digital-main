<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Institution;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'institution_id' => ['nullable', 'integer', 'exists:institutions,id'],
            'action' => ['nullable', 'string', 'max:255'],
            'table_name' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $logs = AuditLog::with(['user', 'institution'])
            ->when($filters['institution_id'] ?? null, fn ($query, $institutionId) => $query->where('institution_id', $institutionId))
            ->when($filters['action'] ?? null, fn ($query, $action) => $query->whereRaw('LOWER(action) LIKE ?', ['%' . mb_strtolower($action) . '%']))
            ->when($filters['table_name'] ?? null, fn ($query, $tableName) => $query->where('table_name', $tableName))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $institutions = Institution::orderBy('name')->get(['id', 'name']);
        $tables = AuditLog::query()
            ->whereNotNull('table_name')
            ->distinct()
            ->orderBy('table_name')
            ->pluck('table_name');

        return view('super_admin.audit_logs.index', compact('logs', 'institutions', 'tables', 'filters'));
    }
}
