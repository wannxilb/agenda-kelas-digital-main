{{-- resources/views/super_admin/institutions/show.blade.php --}}
@extends('layouts.super_admin')

@section('title', __('Detail Instansi'))
@section('header', __('Detail Instansi'))

@section('content')
<div class="max-w-7xl mx-auto pb-10 space-y-8">
    <nav class="flex items-center gap-2">
        <a href="{{ route('super-admin.institutions.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-900 transition-colors">{{ __('Manajemen Instansi') }}</a>
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-sm font-bold text-gray-900">{{ $institution->name }}</span>
    </nav>

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-8 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="flex items-start gap-5">
                <div class="w-16 h-16 bg-linear-to-br from-purple-100 to-indigo-100 text-purple-700 rounded-2xl flex items-center justify-center font-black text-2xl shrink-0 border border-purple-200/70">
                    {{ strtoupper(substr($institution->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">{{ $institution->name }}</h1>
                    <div class="flex flex-wrap items-center gap-3 mt-3">
                        <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-bold uppercase tracking-widest border border-gray-200">ID: {{ $institution->id }}</span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-widest border {{ $institution->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($institution->status === 'suspended' ? 'bg-red-50 text-red-700 border-red-200' : ($institution->status === 'maintenance' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-gray-100 text-gray-600 border-gray-300')) }}">
                            <span class="w-2 h-2 rounded-full {{ $institution->status === 'active' ? 'bg-emerald-500' : ($institution->status === 'suspended' ? 'bg-red-500' : ($institution->status === 'maintenance' ? 'bg-amber-500' : 'bg-gray-400')) }}"></span>
                            {{ ucfirst($institution->status ?? 'unknown') }}
                        </span>
                    </div>
                    <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                        <div class="bg-gray-50 rounded-2xl px-4 py-3 border border-gray-100">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __('Email') }}</p>
                            <p class="font-bold text-gray-900 truncate">{{ $institution->email ?: '-' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-2xl px-4 py-3 border border-gray-100">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __('Telepon') }}</p>
                            <p class="font-bold text-gray-900 truncate">{{ $institution->phone ?: '-' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-2xl px-4 py-3 border border-gray-100">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __('Terdaftar') }}</p>
                            <p class="font-bold text-gray-900">{{ $institution->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('super-admin.institutions.edit', $institution->id) }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-purple-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-purple-500/20 hover:bg-purple-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                {{ __('Edit Instansi') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-4">
        @foreach([
            ['label' => 'Siswa Aktif', 'value' => $stats['students'], 'tone' => 'blue'],
            ['label' => 'Siswa Nonaktif', 'value' => $stats['inactive_students'], 'tone' => 'gray'],
            ['label' => 'Siswa Lulus', 'value' => $stats['graduated_students'], 'tone' => 'emerald'],
            ['label' => 'Guru', 'value' => $stats['teachers'], 'tone' => 'indigo'],
            ['label' => 'Kelas', 'value' => $stats['classes'], 'tone' => 'purple'],
            ['label' => 'Mapel', 'value' => $stats['subjects'], 'tone' => 'amber'],
            ['label' => 'Jadwal', 'value' => $stats['schedules'], 'tone' => 'cyan'],
            ['label' => 'Ruangan', 'value' => $stats['rooms'], 'tone' => 'rose'],
        ] as $item)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __($item['label']) }}</p>
                <p class="mt-2 text-2xl font-black text-gray-900">{{ $item['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <div class="xl:col-span-2 space-y-8">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-black text-gray-900">{{ __('Kontrol Fitur Instansi') }}</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ __('Override fitur untuk sekolah ini, atau ikuti pengaturan global.') }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('super-admin.institutions.features.update', $institution) }}" class="p-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($features as $feature)
                            <div class="border border-gray-100 rounded-2xl p-4 bg-gray-50/60">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-black text-gray-900">{{ $feature['label'] }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $feature['desc'] }}</p>
                                    </div>
                                    <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $feature['enabled'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $feature['enabled'] ? __('Aktif') : __('Nonaktif') }}
                                    </span>
                                </div>
                                <div class="mt-4">
                                    <select name="features[{{ $feature['key'] }}]" class="w-full rounded-xl border-gray-200 text-sm focus:border-purple-500 focus:ring-purple-500">
                                        <option value="inherit" @selected($feature['uses_global'])>{{ __('Ikuti global') }}</option>
                                        <option value="1" @selected($feature['override_value'] === '1')>{{ __('Aktif khusus instansi') }}</option>
                                        <option value="0" @selected($feature['override_value'] === '0')>{{ __('Nonaktif khusus instansi') }}</option>
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-purple-600 text-white rounded-xl text-sm font-bold hover:bg-purple-700 transition-colors">{{ __('Simpan Fitur') }}</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-black text-gray-900">{{ __('Aktivitas Terbaru') }}</h2>
                    <a href="{{ route('super-admin.audit-logs.index', ['institution_id' => $institution->id]) }}" class="text-xs font-bold text-purple-600 hover:text-purple-700">{{ __('Lihat Semua') }}</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($recentLogs as $log)
                        <div class="px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                            <div>
                                <p class="font-bold text-gray-900 text-sm">{{ $log->action }}</p>
                                <p class="text-xs text-gray-500">{{ $log->user?->name ?? __('Sistem') }} - {{ $log->table_name }} #{{ $log->record_id }}</p>
                            </div>
                            <span class="text-xs text-gray-400">{{ $log->created_at->format('d M Y H:i') }}</span>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-sm text-gray-500">{{ __('Belum ada aktivitas untuk instansi ini.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="text-base font-black text-gray-900">{{ __('Operasional Hari Ini') }}</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3">
                        <span class="text-sm font-bold text-gray-600">{{ __('Agenda') }}</span>
                        <span class="text-xl font-black text-gray-900">{{ $stats['agendas_today'] }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3">
                        <span class="text-sm font-bold text-gray-600">{{ __('Presensi Mapel') }}</span>
                        <span class="text-xl font-black text-gray-900">{{ $stats['attendances_today'] }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3">
                        <span class="text-sm font-bold text-gray-600">{{ __('Presensi Harian') }}</span>
                        <span class="text-xl font-black text-gray-900">{{ $stats['daily_attendances_today'] }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-black text-gray-900">{{ __('Admin Instansi') }}</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-green-50 text-green-600 border border-green-100 uppercase tracking-widest">{{ $stats['admins'] }}/2</span>
                </div>
                <div class="p-6">
                    @if($stats['admins'] > 0)
                        <div class="space-y-3">
                            @foreach($institution->users()->role('admin')->get() as $admin)
                                <div class="flex items-center justify-between p-4 rounded-2xl border border-gray-100 bg-gray-50">
                                    <div class="min-w-0">
                                        <p class="font-bold text-gray-900 text-sm truncate">{{ $admin->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $admin->email }}</p>
                                    </div>
                                    <a href="{{ route('super-admin.admins.edit', $admin->id) }}" class="p-2 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="{{ __('Edit Admin') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <p class="text-sm text-gray-500 font-medium mb-3">{{ __('Instansi ini belum memiliki admin.') }}</p>
                            <a href="{{ route('super-admin.admins.create') }}" class="inline-flex items-center gap-2 text-xs font-bold text-purple-600 hover:text-purple-700">{{ __('Tambah Admin Baru') }}</a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-gray-50 rounded-3xl p-6 border border-gray-200 border-dashed">
                <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-3">{{ __('Alamat') }}</p>
                <p class="text-sm font-semibold text-gray-800 leading-relaxed">{{ $institution->address ?: '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
