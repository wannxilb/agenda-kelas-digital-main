{{-- resources/views/sekretaris/dashboard.blade.php --}}
@extends('layouts.sekretaris')

@section('title', 'Dashboard')
@section('header', 'Dashboard Sekretaris')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
@endphp

<div class="space-y-4 sm:space-y-6 pb-24" x-data="{ tab: 'presensi' }">

    {{-- Welcome Banner (Consistent with Siswa) --}}
    <div class="relative overflow-hidden bg-linear-to-br from-blue-600 via-indigo-600 to-indigo-700 rounded-3xl p-4 sm:p-6 text-white shadow-lg shadow-blue-200/60">
        {{-- decorative blobs --}}
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-12 -left-8 w-28 h-28 bg-white/10 rounded-full blur-2xl"></div>

        <div class="relative flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-blue-100/90 tracking-wide">{{ $greeting }},</p>
                <h1 class="text-lg sm:text-2xl font-bold truncate">{{ Auth::user()->name }}</h1>
                <p class="mt-0.5 text-blue-100 text-[11px] sm:text-sm">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>

                <div class="mt-3 flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span class="inline-flex items-center px-2 sm:px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-medium backdrop-blur-sm ring-1 ring-white/10">
                        <svg class="w-3 h-3 mr-1 sm:mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                        </svg>
                        Sekretaris
                    </span>
                    <span class="inline-flex items-center px-2 sm:px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-medium backdrop-blur-sm ring-1 ring-white/10">
                        <svg class="w-3 h-3 mr-1 sm:mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        {{ Auth::user()->class->name ?? 'Belum ada kelas' }}
                    </span>
                </div>
            </div>
            <div class="shrink-0">
                <div class="w-14 h-14 sm:w-20 sm:h-20 bg-white/15 rounded-2xl flex items-center justify-center backdrop-blur-sm ring-1 ring-white/20 shadow-inner">
                    <span class="text-2xl sm:text-4xl font-bold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                </div>
            </div>
        </div>
    </div>


    {{-- Quick Stats (Consistent with Siswa horizontal scroll colors) --}}
    <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-4 gap-3 sm:gap-4 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0 scrollbar-hide">
            
            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4 flex flex-col justify-center relative overflow-hidden">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 z-10">Total Agenda</p>
                <p class="text-xl sm:text-2xl font-black text-gray-900 z-10">{{ $stats['total_agendas'] }}</p>
                <svg class="absolute -bottom-2 -right-2 w-14 h-14 text-gray-50 opacity-50" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"></path></svg>
            </div>

            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-emerald-50/50 rounded-2xl border border-emerald-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Rata Presensi</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600">{{ $stats['avg_attendance'] }}%</p>
            </div>

            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-amber-50/50 rounded-2xl border border-amber-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-1">Total Kelas</p>
                <p class="text-xl sm:text-2xl font-black text-amber-600">{{ $stats['total_classes'] }}</p>
            </div>

            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-sky-50/50 rounded-2xl border border-sky-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-sky-600/70 uppercase tracking-wider mb-1">Total Siswa</p>
                <p class="text-xl sm:text-2xl font-black text-sky-600">{{ $stats['total_students'] }}</p>
            </div>
            
        </div>
        <p class="sm:hidden mt-2 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">← Geser →</p>
    </div>

    {{-- Mobile Tab Switcher (Consistent with Siswa) --}}
    <div class="lg:hidden flex bg-gray-100 rounded-2xl p-1 gap-1">
        <button type="button" @click="tab = 'presensi'"
            :class="tab === 'presensi' ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-500 hover:text-gray-700'"
            class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Presensi
        </button>
        <button type="button" @click="tab = 'agenda'"
            :class="tab === 'agenda' ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-500 hover:text-gray-700'"
            class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Agenda
        </button>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

        {{-- Left Column: Presensi Hari Ini (Mobile View default) --}}
        <div :class="{ 'hidden': tab === 'agenda' }" class="lg:block lg:col-span-2 space-y-4 sm:space-y-6">
            
            {{-- Presensi Hari Ini --}}
            <div :class="{ 'hidden': tab !== 'presensi' }" class="lg:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <h3 class="font-bold text-sm sm:text-base text-gray-800">Presensi Hari Ini</h3>
                    </div>
                    <a href="{{ route('sekretaris.attendance.index') }}"
                       class="text-[11px] sm:text-xs font-medium text-blue-600 hover:underline active:text-blue-800">
                        Detail →
                    </a>
                </div>
                
                <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    @forelse($today_attendance as $attendance)
                        @php 
                            $percentage = $attendance['total'] > 0 ? ($attendance['present'] / $attendance['total']) * 100 : 0; 
                            $colorClass = $percentage >= 90 ? 'emerald' : ($percentage >= 70 ? 'amber' : 'rose');
                        @endphp
                        <div class="p-4 sm:p-5 bg-gray-50/50 rounded-2xl border border-gray-100 hover:border-blue-200 transition-colors">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-sm font-bold text-gray-900">{{ $attendance['class_name'] }}</span>
                                <span class="text-[10px] sm:text-xs font-medium text-gray-600 bg-white shadow-sm px-2.5 py-1 rounded-lg border border-gray-100">
                                    {{ $attendance['present'] }} / {{ $attendance['total'] }} Siswa
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-1000"
                                     style="width: {{ $percentage }}%; {{ $percentage >= 90 ? 'background-color: #10B981' : ($percentage >= 70 ? 'background-color: #F59E0B' : 'background-color: #EF4444') }}"></div>
                            </div>
                            <div class="flex items-center justify-between mt-2.5">
                                <p class="text-[10px] text-gray-500 font-medium tracking-wide">Tingkat Kehadiran</p>
                                <p class="text-[11px] font-bold" style="color: {{ $percentage >= 90 ? '#10B981' : ($percentage >= 70 ? '#F59E0B' : '#EF4444') }}">{{ number_format($percentage, 0) }}%</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-1 sm:col-span-2 py-10 text-center">
                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <p class="text-sm font-bold text-gray-600">Belum Ada Presensi</p>
                            <p class="text-xs text-gray-400 mt-1">Data presensi hari ini belum dimasukkan.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Right Column: Agenda Terbaru (Mobile View default hidden) --}}
        <div :class="{ 'hidden': tab !== 'agenda' }" class="lg:block lg:col-span-1 space-y-4 sm:space-y-6">
            {{-- Agenda Terbaru --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col max-h-125 lg:max-h-150">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="font-bold text-sm sm:text-base text-gray-800">Agenda Terbaru</h3>
                    </div>
                    <a href="{{ route('sekretaris.agenda.index') }}" class="text-[11px] sm:text-xs font-medium text-blue-600 hover:underline active:text-blue-800">Semua →</a>
                </div>
                <div class="flex-1 overflow-y-auto divide-y divide-gray-50 custom-scrollbar">
                    @forelse($recent_agendas as $agenda)
                        <div class="px-4 sm:px-5 py-3 sm:py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $agenda->title }}</h4>
                                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ strip_tags($agenda->description) }}</p>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <span class="text-[11px] text-gray-400">{{ \Carbon\Carbon::parse($agenda->date)->translatedFormat('d M Y') }}</span>
                                        <span class="text-[11px] text-gray-400">•</span>
                                        <span class="text-[11px] text-gray-400 truncate">{{ $agenda->class->name ?? '-' }}</span>
                                    </div>
                                </div>
                                @if($agenda->subject)
                                <span class="shrink-0 px-2 py-0.5 text-[10px] sm:text-xs bg-indigo-100 text-indigo-700 rounded-full font-medium truncate max-w-20">{{ $agenda->subject->name }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center flex flex-col items-center justify-center h-full">
                            <div class="w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <p class="text-[11px] font-bold text-gray-500 uppercase tracking-widest">Belum Ada Agenda</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik Kehadiran --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Grafik Kehadiran</h3>
                    <p class="text-xs text-gray-500">6 bulan terakhir</p>
                </div>
            </div>
            <span class="text-[10px] font-bold text-gray-400 bg-gray-50 border border-gray-100 px-2.5 py-1 rounded-lg">6 Bulan</span>
        </div>
        <div class="px-1 sm:px-4 pt-2 pb-3 sm:pb-4">
            <div id="attendanceChart" class="w-full"></div>
        </div>
    </div>

    {{-- Tips Box --}}
    <div class="bg-linear-to-br from-blue-50 to-indigo-50 rounded-2xl p-5 border border-blue-100 relative overflow-hidden">
        <svg class="absolute -right-4 -bottom-4 w-24 h-24 text-blue-500/10" fill="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="relative z-10">
            <h3 class="text-[11px] font-bold text-blue-800 tracking-wide mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Informasi
            </h3>
            <ul class="space-y-1.5 text-xs text-blue-700/90 font-medium">
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Isi agenda kelas sebelum jam pelajaran terakhir selesai.
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Gunakan menu Cetak Laporan untuk rekap bulanan.
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const monthlyData = Object.values(
            (@json($monthly_attendance) || []).reduce(function(items, item) {
                items[item.month_key || item.month] = item;
                return items;
            }, {})
        );
        if (!monthlyData || monthlyData.length === 0) return;

        const chartEl = document.querySelector("#attendanceChart");
        if (!chartEl || typeof ApexCharts === 'undefined') return;

        if (window.sekretarisAttendanceChart) {
            window.sekretarisAttendanceChart.destroy();
        }
        chartEl.innerHTML = '';

        const isMobile = window.innerWidth < 640;
        const monthLabels = monthlyData.map(item => item.month);

        var options = {
            series: [{
                name: 'Hadir',
                data: monthlyData.map(item => item.present)
            }, {
                name: 'Terlambat',
                data: monthlyData.map(item => item.late)
            }, {
                name: 'Alpha',
                data: monthlyData.map(item => item.absent)
            }, {
                name: 'Izin',
                data: monthlyData.map(item => item.excused)
            }, {
                name: 'Sakit',
                data: monthlyData.map(item => item.sick)
            }],
            chart: {
                height: isMobile ? 240 : 280,
                width: '100%',
                type: 'bar',
                stacked: true,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                animations: { enabled: true, speed: 400 }
            },
            plotOptions: {
                bar: {
                    columnWidth: isMobile ? '58%' : '45%',
                    borderRadius: isMobile ? 3 : 5,
                    borderRadiusApplication: 'end'
                }
            },
            colors: ['#10B981', '#F59E0B', '#EF4444', '#3B82F6', '#F97316'],
            xaxis: {
                categories: monthLabels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    show: true,
                    rotate: 0,
                    rotateAlways: false,
                    hideOverlappingLabels: false,
                    trim: true,
                    formatter: function(value) {
                        return isMobile && typeof value === 'string' ? value.slice(0, 3) : value;
                    },
                    style: { colors: '#94a3b8', fontSize: isMobile ? '9px' : '11px', fontWeight: 600 }
                }
            },
            yaxis: {
                labels: { style: { colors: '#94a3b8', fontSize: isMobile ? '9px' : '11px' } }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                yaxis: { lines: { show: true } },
                xaxis: { lines: { show: false } }
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: isMobile ? '10px' : '11px',
                fontWeight: 600,
                markers: { size: isMobile ? 6 : 8, shape: 'square' },
                itemMargin: { horizontal: isMobile ? 6 : 8, vertical: 4 }
            },
            tooltip: {
                theme: 'light',
                shared: true,
                intersect: false
            },
            dataLabels: { enabled: false }
        };

        window.sekretarisAttendanceChart = new ApexCharts(chartEl, options);
        window.sekretarisAttendanceChart.render();
    });
</script>
@endpush
