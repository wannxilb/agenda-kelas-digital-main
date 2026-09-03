{{-- resources/views/siswa/dashboard.blade.php --}}
@extends('layouts.siswa')

@section('title', 'Dashboard')
@section('header', 'Dashboard Siswa')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
@endphp

<div class="space-y-4 sm:space-y-6" x-data="{ tab: 'agenda' }">

    @if($noClass ?? false)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 sm:p-6 text-center">
        <svg class="w-10 h-10 sm:w-12 sm:h-12 mx-auto text-amber-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <h3 class="text-base sm:text-lg font-bold text-amber-800">Belum Ada Kelas</h3>
        <p class="text-xs sm:text-sm text-amber-600 mt-1">Akun Anda belum ditugaskan ke kelas. Hubungi admin sekolah.</p>
    </div>
    @endif

    {{-- Welcome Banner --}}
    <div class="relative overflow-hidden bg-linear-to-br from-blue-600 via-indigo-600 to-indigo-700 rounded-3xl p-4 sm:p-6 text-white shadow-lg shadow-blue-200/60">
        {{-- decorative blobs, hidden on very small screens for cleanliness --}}
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-12 -left-8 w-28 h-28 bg-white/10 rounded-full blur-2xl"></div>

        <div class="relative flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-blue-100/90 tracking-wide">{{ $greeting }},</p>
                <h1 class="text-lg sm:text-2xl font-bold truncate">{{ $user->name }}</h1>
                <p class="mt-0.5 text-blue-100 text-[11px] sm:text-sm">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>

                <div class="mt-3 flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span class="inline-flex items-center px-2 sm:px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-medium backdrop-blur-sm ring-1 ring-white/10">
                        <svg class="w-3 h-3 mr-1 sm:mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                        </svg>
                        NIS: {{ $user->nis ?? '-' }}
                    </span>
                    <span class="inline-flex items-center px-2 sm:px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-medium backdrop-blur-sm ring-1 ring-white/10">
                        <svg class="w-3 h-3 mr-1 sm:mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        {{ $user->class->name ?? 'Belum ada kelas' }}
                    </span>
                </div>
            </div>
            <div class="shrink-0">
                <div class="w-14 h-14 sm:w-20 sm:h-20 bg-white/15 rounded-2xl flex items-center justify-center backdrop-blur-sm ring-1 ring-white/20">
                    <span class="text-2xl sm:text-4xl font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards: horizontal scroll-snap on mobile, grid on larger screens --}}
    <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0 scrollbar-hide">
            
            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4 flex flex-col justify-center relative overflow-hidden">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 z-10">Total Pertemuan</p>
                <p class="text-xl sm:text-2xl font-black text-gray-900 z-10">{{ $attendance_stats['total'] }}</p>
                <svg class="absolute -bottom-2 -right-2 w-14 h-14 text-gray-50 opacity-50" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"></path></svg>
            </div>

            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-emerald-50/50 rounded-2xl border border-emerald-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Hadir</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600">{{ $attendance_stats['present'] ?? 0 }}</p>
            </div>

            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-amber-50/50 rounded-2xl border border-amber-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-1">Terlambat</p>
                <p class="text-xl sm:text-2xl font-black text-amber-600">{{ $attendance_stats['late'] ?? 0 }}</p>
            </div>

            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-sky-50/50 rounded-2xl border border-sky-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-sky-600/70 uppercase tracking-wider mb-1">Izin / Sakit</p>
                <p class="text-xl sm:text-2xl font-black text-sky-600">{{ ($attendance_stats['excused'] ?? 0) + ($attendance_stats['sick'] ?? 0) }}</p>
            </div>

            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-rose-50/50 rounded-2xl border border-rose-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-rose-600/70 uppercase tracking-wider mb-1">Alpha</p>
                <p class="text-xl sm:text-2xl font-black text-rose-600">{{ $attendance_stats['absent'] ?? 0 }}</p>
            </div>

            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-purple-50 rounded-2xl border border-purple-100 p-3.5 sm:p-4 flex flex-col justify-center relative overflow-hidden">
                <div class="absolute inset-0 bg-linear-to-br from-purple-100/50 to-transparent"></div>
                <p class="text-[9px] sm:text-[10px] font-bold text-purple-600/70 uppercase tracking-wider mb-1 relative z-10">Kehadiran</p>
                <p class="text-xl sm:text-2xl font-black text-purple-600 relative z-10">{{ $attendance_stats['percentage'] }}%</p>
            </div>
            
        </div>
        <p class="sm:hidden mt-2 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">← Geser →</p>
    </div>

    {{-- Mobile Tab Switcher (hidden on lg+, where both panels show side by side) --}}
    <div class="lg:hidden flex bg-gray-100 rounded-2xl p-1 gap-1">
        <button type="button" @click="tab = 'agenda'"
            :class="tab === 'agenda' ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-500'"
            class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Agenda
        </button>
        <button type="button" @click="tab = 'jadwal'"
            :class="tab === 'jadwal' ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-500'"
            class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Jadwal
        </button>
    </div>

    {{-- Agenda Terbaru & Jadwal Hari Ini --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">

        {{-- Agenda Terbaru --}}
        <div :class="{ 'hidden': tab !== 'agenda' }" class="lg:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-sm sm:text-base text-gray-800">Agenda Terbaru</h3>
                </div>
                <a href="{{ route('siswa.agenda.index') }}" class="text-[11px] sm:text-xs font-medium text-blue-600 hover:underline active:text-blue-800">Lihat semua →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recent_agendas as $agenda)
                <div class="px-4 sm:px-5 py-3 sm:py-4 active:bg-gray-50 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $agenda->title }}</h4>
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ strip_tags($agenda->description) }}</p>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="text-[11px] text-gray-400">{{ \Carbon\Carbon::parse($agenda->date)->translatedFormat('d F Y') }}</span>
                                <span class="text-[11px] text-gray-400">•</span>
                                <span class="text-[11px] text-gray-400 truncate">{{ $agenda->teacher->name ?? '-' }}</span>
                            </div>
                        </div>
                        @if($agenda->subject)
                        <span class="shrink-0 px-2 py-0.5 text-[10px] sm:text-xs bg-indigo-100 text-indigo-700 rounded-full font-medium">{{ $agenda->subject->name }}</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="px-4 py-8 sm:py-10 text-center">
                    <svg class="w-10 h-10 sm:w-12 sm:h-12 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-xs sm:text-sm text-gray-500 italic">Belum ada agenda</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Jadwal Hari Ini --}}
        <div :class="{ 'hidden': tab !== 'jadwal' }" class="lg:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-green-50 text-green-600 rounded-lg">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-gray-800">Jadwal Hari Ini</h3>
                        <p class="text-[11px] sm:text-xs text-gray-500">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>
                <a href="{{ route('siswa.schedule.index') }}" class="text-[11px] sm:text-xs font-medium text-blue-600 hover:underline active:text-blue-800">Semua →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($today_schedules as $schedule)
                @php
                    $teacherStatus = $schedule->teacherStatusForStudent;
                    $statusLabel = match ($teacherStatus?->type) {
                        'tugas_luar' => 'Tugas luar',
                        'sakit' => 'Guru sakit',
                        default => 'Guru berhalangan',
                    };
                    $statusClass = match ($teacherStatus?->type) {
                        'tugas_luar' => 'bg-sky-50 text-sky-700 border-sky-100',
                        'sakit' => 'bg-orange-50 text-orange-700 border-orange-100',
                        default => 'bg-amber-50 text-amber-700 border-amber-100',
                    };
                @endphp
                <div class="px-4 sm:px-5 py-3 sm:py-4 active:bg-gray-50 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="shrink-0 text-center w-12 sm:w-14">
                            <p class="text-xs sm:text-sm font-bold text-blue-600">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}</p>
                            <p class="text-[10px] sm:text-xs text-gray-400">{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</p>
                        </div>
                        <div class="w-px h-7 bg-gray-200 shrink-0"></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $schedule->subject->name ?? '-' }}</p>
                            <p class="text-[11px] sm:text-xs text-gray-500 truncate">{{ $schedule->teacher->name ?? '-' }}</p>
                            @if($teacherStatus)
                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                    <span class="inline-flex items-center rounded-md border px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                    @if($teacherStatus->substituteTeacher)
                                        <span class="text-[10px] font-semibold text-emerald-700 truncate">
                                            Digantikan {{ $teacherStatus->substituteTeacher->name }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        @if($schedule->room)
                        <span class="shrink-0 text-[10px] sm:text-xs text-gray-400 bg-gray-100 px-2 py-0.5 sm:py-1 rounded-lg">{{ $schedule->room }}</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="px-4 py-8 sm:py-10 text-center">
                    <svg class="w-10 h-10 sm:w-12 sm:h-12 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-xs sm:text-sm text-gray-500 italic">Tidak ada jadwal hari ini</p>
                </div>
                @endforelse
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
</div>
@endsection

@push('styles')
<style>
    /* hide scrollbar on the mobile stats strip while keeping it scrollable */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
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

        if (window.siswaAttendanceChart) {
            window.siswaAttendanceChart.destroy();
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
            dataLabels: { enabled: false },
            tooltip: {
                theme: 'light',
                shared: true,
                intersect: false
            }
        };

        window.siswaAttendanceChart = new ApexCharts(chartEl, options);
        window.siswaAttendanceChart.render();
    });
</script>
@endpush
