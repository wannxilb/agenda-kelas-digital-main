@extends('layouts.super_admin')

@section('title', __('Log Audit'))
@section('header', __('Log Audit'))

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">{{ __('Log Audit Sistem') }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ __('Telusuri aktivitas lintas instansi, tabel, dan rentang tanggal.') }}</p>
            </div>
            <span class="text-xs text-gray-500 bg-gray-50 px-3 py-1 rounded-full border border-gray-100 w-fit">{{ $logs->total() }} total</span>
        </div>

        <form method="GET" action="{{ route('super-admin.audit-logs.index') }}" class="p-6 bg-gray-50/70 border-b border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">{{ __('Instansi') }}</label>
                    <select name="institution_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-purple-500 focus:ring-purple-500">
                        <option value="">{{ __('Semua instansi') }}</option>
                        @foreach($institutions as $institution)
                            <option value="{{ $institution->id }}" @selected(request('institution_id') == $institution->id)>{{ $institution->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">{{ __('Aksi') }}</label>
                    <input type="text" name="action" value="{{ request('action') }}" placeholder="{{ __('Contoh: Mengubah') }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-purple-500 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">{{ __('Tabel') }}</label>
                    <select name="table_name" class="w-full rounded-xl border-gray-200 text-sm focus:border-purple-500 focus:ring-purple-500">
                        <option value="">{{ __('Semua tabel') }}</option>
                        @foreach($tables as $table)
                            <option value="{{ $table }}" @selected(request('table_name') === $table)>{{ $table }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">{{ __('Dari') }}</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-purple-500 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">{{ __('Sampai') }}</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-purple-500 focus:ring-purple-500">
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-3">
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-bold hover:bg-purple-700 transition-colors">{{ __('Terapkan Filter') }}</button>
                <a href="{{ route('super-admin.audit-logs.index') }}" class="px-4 py-2 bg-white text-gray-700 rounded-xl text-sm font-bold border border-gray-200 hover:bg-gray-50 transition-colors">{{ __('Reset') }}</a>
            </div>
        </form>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">{{ __('Waktu') }}</th>
                        <th class="px-6 py-3">{{ __('Instansi') }}</th>
                        <th class="px-6 py-3">{{ __('Pengguna') }}</th>
                        <th class="px-6 py-3">{{ __('Aksi') }}</th>
                        <th class="px-6 py-3">{{ __('Tabel') }}</th>
                        <th class="px-6 py-3">{{ __('ID') }}</th>
                        <th class="px-6 py-3">{{ __('IP') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-6 py-4 max-w-[220px] truncate" title="{{ $log->institution?->name ?? '-' }}">{{ $log->institution?->name ?? '-' }}</td>
                            <td class="px-6 py-4 max-w-[200px] truncate" title="{{ $log->user?->name ?? __('Sistem') }}">{{ $log->user?->name ?? __('Sistem') }}</td>
                            <td class="px-6 py-4"><span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-lg text-xs font-semibold whitespace-nowrap">{{ $log->action }}</span></td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $log->table_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $log->record_id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">{{ __('Belum ada log yang ditemukan.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden divide-y divide-gray-100">
            @forelse($logs as $log)
                <div class="p-4 space-y-2 hover:bg-gray-50">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs text-gray-400">{{ $log->created_at->format('Y-m-d H:i:s') }}</span>
                        <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded-lg text-xs font-semibold shrink-0">{{ $log->action }}</span>
                    </div>
                    <div class="text-sm font-bold text-gray-900 truncate">{{ $log->institution?->name ?? '-' }}</div>
                    <div class="text-sm font-medium text-gray-700 truncate">{{ $log->user?->name ?? __('Sistem') }}</div>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                        <span>Tabel: <strong class="text-gray-700">{{ $log->table_name }}</strong></span>
                        <span>ID: <strong class="text-gray-700">{{ $log->record_id }}</strong></span>
                        <span>IP: <strong class="text-gray-700 font-mono">{{ $log->ip_address }}</strong></span>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">{{ __('Belum ada log yang ditemukan.') }}</div>
            @endforelse
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
