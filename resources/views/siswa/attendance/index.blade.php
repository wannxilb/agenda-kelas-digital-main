{{-- resources/views/siswa/attendance/index.blade.php --}}
@extends('layouts.siswa')

@section('title', 'Presensi Saya')
@section('header', 'Presensi Saya')

@section('content')
@php
    $statusThemes = [
        'present' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-700', 'soft' => 'bg-emerald-50', 'border' => 'border-emerald-100', 'ring' => 'ring-emerald-500/20', 'icon' => '✓', 'label' => 'Hadir'],
        'absent'  => ['bg' => 'bg-rose-500', 'text' => 'text-rose-700', 'soft' => 'bg-rose-50', 'border' => 'border-rose-100', 'ring' => 'ring-rose-500/20', 'icon' => '✗', 'label' => 'Alpha'],
        'late'    => ['bg' => 'bg-amber-500', 'text' => 'text-amber-700', 'soft' => 'bg-amber-50', 'border' => 'border-amber-100', 'ring' => 'ring-amber-500/20', 'icon' => '⏰', 'label' => 'Terlambat'],
        'sick'    => ['bg' => 'bg-orange-500', 'text' => 'text-orange-700', 'soft' => 'bg-orange-50', 'border' => 'border-orange-100', 'ring' => 'ring-orange-500/20', 'icon' => '🤒', 'label' => 'Sakit'],
        'excused' => ['bg' => 'bg-sky-500', 'text' => 'text-sky-700', 'soft' => 'bg-sky-50', 'border' => 'border-sky-100', 'ring' => 'ring-sky-500/20', 'icon' => '📋', 'label' => 'Izin'],
    ];
    $statusThemes['present']['icon'] = 'check';
    $statusThemes['absent']['icon'] = 'x';
    $statusThemes['late']['icon'] = 'clock';
    $statusThemes['sick']['icon'] = 'medical';
    $statusThemes['excused']['icon'] = 'clipboard';
@endphp

<div class="space-y-5 sm:space-y-6">
    {{-- Top Description --}}
    <div>
        <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
            Pantau riwayat kehadiran, statistik bulanan, dan status presensi harian Anda.
        </p>
    </div>

    {{-- Today's Status Banner --}}
    @if($today_attendance)
        @php $theme = $statusThemes[$today_attendance->status]; @endphp
        <div class="relative overflow-hidden rounded-xl sm:rounded-2xl {{ $theme['bg'] }} text-white shadow-lg sm:shadow-xl transition-transform active:scale-[0.98]">
            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-32 h-32 bg-white/20 rounded-full blur-2xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-24 h-24 bg-black/10 rounded-full blur-xl"></div>
            
            <div class="relative p-4 sm:p-5 flex items-center justify-between gap-3 sm:gap-4">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center shadow-inner border border-white/30 shrink-0">
                        @switch($theme['icon'])
                            @case('x')
                                <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                @break
                            @case('clock')
                                <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"></path></svg>
                                @break
                            @case('medical')
                                <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 3h6v6h6v6h-6v6H9v-6H3V9h6V3z"></path></svg>
                                @break
                            @case('clipboard')
                                <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5h6m-7 4h8m-8 4h5m-7 8h10a2 2 0 002-2V7.5A2.5 2.5 0 0015.5 5h-7A2.5 2.5 0 006 7.5V19a2 2 0 002 2z"></path></svg>
                                @break
                            @default
                                <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        @endswitch
                    </div>
                    <div>
                        <p class="text-[9px] sm:text-[10px] font-bold text-white/80 uppercase tracking-widest mb-0.5">Status Hari Ini</p>
                        <h2 class="text-lg sm:text-xl font-black tracking-tight">{{ $theme['label'] }}</h2>
                        <div class="flex items-center gap-1.5 mt-0.5 sm:mt-1 opacity-90">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-[11px] sm:text-xs font-semibold">
                                {{ $today_attendance->check_in_time ? \Carbon\Carbon::parse($today_attendance->check_in_time)->format('H:i') : 'Tanpa Jam' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="text-right hidden sm:block">
                    <p class="text-[9px] sm:text-[10px] font-bold text-white/80 uppercase tracking-widest mb-0.5">Tanggal</p>
                    <p class="text-sm sm:text-base font-bold">{{ $today_attendance->date->translatedFormat('d M Y') }}</p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-gray-50/80 backdrop-blur-sm rounded-2xl border border-dashed border-gray-300 p-6 sm:p-8 text-center">
            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm border border-gray-100">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <p class="text-sm font-bold text-gray-800">Belum Ada Presensi</p>
            <p class="text-xs text-gray-500 mt-1">Presensi hari ini belum dicatat oleh guru/admin.</p>
        </div>
    @endif

    {{-- Horizontal Scrollable Stats on Mobile --}}
    <div class="relative -mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0">
            
            <div class="snap-center shrink-0 w-35 sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5 flex flex-col justify-center relative overflow-hidden">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 z-10">Total Pertemuan</p>
                <p class="text-2xl sm:text-3xl font-black text-gray-900 z-10">{{ $stats['total'] }}</p>
                <svg class="absolute -bottom-2 -right-2 w-16 h-16 text-gray-50 opacity-50" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"></path></svg>
            </div>

            <div class="snap-center shrink-0 w-35 sm:w-auto bg-emerald-50/50 rounded-2xl border border-emerald-100 p-4 sm:p-5 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Hadir</p>
                <p class="text-2xl sm:text-3xl font-black text-emerald-600">{{ $stats['present'] ?? 0 }}</p>
            </div>

            <div class="snap-center shrink-0 w-35 sm:w-auto bg-amber-50/50 rounded-2xl border border-amber-100 p-4 sm:p-5 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-1">Terlambat</p>
                <p class="text-2xl sm:text-3xl font-black text-amber-600">{{ $stats['late'] ?? 0 }}</p>
            </div>

            <div class="snap-center shrink-0 w-35 sm:w-auto bg-sky-50/50 rounded-2xl border border-sky-100 p-4 sm:p-5 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-sky-600/70 uppercase tracking-wider mb-1">Izin / Sakit</p>
                <p class="text-2xl sm:text-3xl font-black text-sky-600">{{ ($stats['excused'] ?? 0) + ($stats['sick'] ?? 0) }}</p>
            </div>

            <div class="snap-center shrink-0 w-35 sm:w-auto bg-rose-50/50 rounded-2xl border border-rose-100 p-4 sm:p-5 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-rose-600/70 uppercase tracking-wider mb-1">Alpha</p>
                <p class="text-2xl sm:text-3xl font-black text-rose-600">{{ $stats['absent'] ?? 0 }}</p>
            </div>

            <div class="snap-center shrink-0 w-35 sm:w-auto bg-purple-50 rounded-2xl border border-purple-100 p-4 sm:p-5 flex flex-col justify-center relative overflow-hidden">
                <div class="absolute inset-0 bg-linear-to-br from-purple-100/50 to-transparent"></div>
                <p class="text-[9px] sm:text-[10px] font-bold text-purple-600/70 uppercase tracking-wider mb-1 relative z-10">Kehadiran</p>
                <p class="text-2xl sm:text-3xl font-black text-purple-600 relative z-10">{{ $stats['percentage'] }}%</p>
            </div>
            
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6">
        
        {{-- Calendar Section --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 overflow-hidden">
                {{-- Calendar Header --}}
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-sm sm:text-base font-bold text-gray-900">Kalender</h3>
                    
                    <form method="GET" action="{{ route('siswa.attendance.index') }}" class="relative">
                        <select name="month" onchange="this.form.requestSubmit()" 
                                class="appearance-none bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pl-4 pr-9 py-2 sm:py-2.5 cursor-pointer">
                            @foreach($months as $value => $label)
                                <option value="{{ $value }}" {{ request('month', date('Y-m')) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </form>
                </div>

                {{-- Calendar Grid --}}
                <div class="grid grid-cols-7 gap-1 sm:gap-2">
                    @php
                        $daysOfWeek = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                        $firstDayOfMonth = \Carbon\Carbon::parse(request('month', date('Y-m')))->startOfMonth();
                        $lastDayOfMonth = \Carbon\Carbon::parse(request('month', date('Y-m')))->endOfMonth();
                        $startDay = $firstDayOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                        $endDay = $lastDayOfMonth->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
                        
                        $attendanceMap = [];
                        foreach($attendances as $att) {
                            $attendanceMap[$att->date->format('Y-m-d')] = $att;
                        }
                    @endphp
                    
                    @foreach($daysOfWeek as $day)
                        <div class="text-[10px] font-bold text-gray-400 uppercase text-center py-2">{{ $day }}</div>
                    @endforeach
                    
                    @for($date = $startDay->copy(); $date <= $endDay; $date->addDay())
                        @php
                            $dateStr = $date->format('Y-m-d');
                            $isCurrentMonth = $date->month == \Carbon\Carbon::parse(request('month', date('Y-m')))->month;
                            $att = $attendanceMap[$dateStr] ?? null;
                            $isToday = $date->isToday();
                        @endphp
                        
                        <div class="relative group aspect-square p-0.5 sm:p-1">
                            <div class="w-full h-full rounded-xl flex flex-col items-center justify-center transition-all duration-200 
                                        {{ $isCurrentMonth ? 'bg-white' : 'bg-gray-50/30 opacity-40' }}
                                        {{ $isToday ? 'ring-2 ring-blue-500 ring-offset-2' : 'border border-gray-100 group-hover:border-blue-200 group-hover:bg-blue-50/30' }}
                                        cursor-default">
                                <span class="text-[11px] sm:text-xs font-semibold {{ $isToday ? 'text-blue-600 font-bold' : ($isCurrentMonth ? 'text-gray-700' : 'text-gray-400') }}">
                                    {{ $date->format('d') }}
                                </span>
                                
                                @if($att)
                                    @php $t = $statusThemes[$att->status]; @endphp
                                    <div class="mt-1 sm:mt-1.5 w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full {{ $t['bg'] }} ring-2 ring-white" title="{{ $t['label'] }}"></div>
                                @else
                                    <div class="mt-1 sm:mt-1.5 w-1.5 h-1.5 sm:w-2 sm:h-2"></div> {{-- Placeholder to keep height --}}
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>

                {{-- Legend --}}
                <div class="mt-6 pt-4 border-t border-gray-100 flex flex-wrap items-center justify-center gap-3 sm:gap-5">
                    @foreach($statusThemes as $key => $t)
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 rounded-full {{ $t['bg'] }}"></div>
                            <span class="text-[10px] font-medium text-gray-500 uppercase tracking-wider">{{ $t['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- History Section --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col h-full overflow-hidden max-h-125 lg:max-h-150">
                <div class="p-4 sm:p-5 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Riwayat Presensi</h3>
                        <p class="text-[10px] text-gray-500 mt-0.5">Bulan ini</p>
                    </div>
                </div>
                
                <div class="flex-1 overflow-y-auto divide-y divide-gray-50 custom-scrollbar">
                    @forelse($attendances as $attendance)
                        @php $t = $statusThemes[$attendance->status]; @endphp
                        <div class="p-4 sm:p-5 hover:bg-gray-50/50 transition-colors flex items-center gap-4">
                            {{-- Date Box --}}
                            <div class="w-12 h-12 rounded-xl {{ $t['soft'] }} flex flex-col items-center justify-center shrink-0 border {{ $t['border'] }}">
                                <span class="text-[10px] font-bold uppercase {{ $t['text'] }} opacity-80">{{ $attendance->date->translatedFormat('M') }}</span>
                                <span class="text-lg font-black {{ $t['text'] }} leading-none">{{ $attendance->date->format('d') }}</span>
                            </div>
                            
                            {{-- Details --}}
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 truncate">{{ $t['label'] }}</h4>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="text-xs text-gray-500 font-medium">
                                        {{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : 'Tanpa Jam' }}
                                    </span>
                                </div>
                            </div>
                            
                            {{-- Status Icon --}}
                            <div class="shrink-0 w-8 h-8 rounded-full {{ $t['bg'] }} text-white flex items-center justify-center shadow-sm">
                                @switch($t['icon'])
                                    @case('x')
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        @break
                                    @case('clock')
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"></path></svg>
                                        @break
                                    @case('medical')
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 3h6v6h6v6h-6v6H9v-6H3V9h6V3z"></path></svg>
                                        @break
                                    @case('clipboard')
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5h6m-7 4h8m-8 4h5m-7 8h10a2 2 0 002-2V7.5A2.5 2.5 0 0015.5 5h-7A2.5 2.5 0 006 7.5V19a2 2 0 002 2z"></path></svg>
                                        @break
                                    @default
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                @endswitch
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center flex flex-col items-center justify-center h-full">
                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <p class="text-sm font-bold text-gray-700">Belum ada riwayat</p>
                            <p class="text-xs text-gray-400 mt-1">Data presensi akan muncul di sini</p>
                        </div>
                    @endforelse
                </div>

                @if($attendances->hasPages())
                    <div class="p-3 bg-white border-t border-gray-100">
                        {{ $attendances->links('vendor.pagination.tailwind-compact') ?? $attendances->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Hide scrollbar for Chrome, Safari and Opera */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    /* Hide scrollbar for IE, Edge and Firefox */
    .hide-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
    
    /* Custom scrollbar for history list */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e5e7eb;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #d1d5db;
    }
</style>
@endpush
