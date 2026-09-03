@extends('layouts.walikelas')

@section('title', 'Rekap Semester')
@section('header', 'Rekap Semester')
@section('content-padding', 'py-4 sm:py-8 pb-28 lg:pb-8')

@section('content')
@php
    $circumference = 97.4;
    $rateDash = round(($attendanceRate / 100) * $circumference, 1);
@endphp

@if($has_class)
<div class="space-y-4 sm:space-y-6 pb-24 lg:pb-8"
     x-data="{ expandedMonth: null }">
    @include('walikelas.partials.context-filter')

    {{-- Hero --}}
    <div class="bg-linear-to-br from-blue-600 via-indigo-600 to-indigo-700 rounded-3xl p-4 sm:p-6 text-white shadow-lg shadow-blue-200/60 relative overflow-hidden">
        <div class="absolute -top-8 -right-8 w-28 h-28 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-10 -left-6 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>

        <div class="relative flex items-center gap-4">
            <div class="relative shrink-0 w-16 h-16 sm:w-20 sm:h-20">
                <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.5" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="3"/>
                    <circle cx="18" cy="18" r="15.5" fill="none" stroke="white" stroke-width="3"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $rateDash }}, {{ $circumference }}"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-sm sm:text-base font-black">{{ number_format($attendanceRate, 1) }}%</span>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] sm:text-xs font-semibold text-blue-100/90 tracking-wide">Rekap semester kelas</p>
                <h1 class="mt-0.5 text-lg sm:text-2xl font-black truncate">{{ $class->name }}</h1>
                <div class="mt-2 sm:mt-3 flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-bold backdrop-blur-sm ring-1 ring-white/10">
                        {{ $totalStudents }} siswa
                    </span>
                    @if($academicYear)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-bold backdrop-blur-sm ring-1 ring-white/10">
                            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                            {{ $academicYear->name }} — {{ $academicYear->semester }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-4 gap-2.5 sm:gap-3 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0">
            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                <div class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <p class="text-xl sm:text-2xl font-black text-gray-900">{{ $totalStudents }}</p>
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-500/80 uppercase tracking-wider mt-0.5">Siswa</p>
            </div>

            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100 shadow-sm">
                <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-xl sm:text-2xl font-black text-emerald-700">{{ number_format($attendanceRate, 1) }}%</p>
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mt-0.5">Rata-rata Kehadiran</p>
            </div>

            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-amber-50/50 p-4 rounded-2xl border border-amber-100 shadow-sm">
                <div class="w-8 h-8 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <p class="text-xl sm:text-2xl font-black text-amber-700">{{ $schoolDays }}</p>
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mt-0.5">Hari Efektif</p>
            </div>

            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-rose-50/50 p-4 rounded-2xl border border-rose-100 shadow-sm">
                <div class="w-8 h-8 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                </div>
                <p class="text-xl sm:text-2xl font-black text-rose-700">{{ $totalAbsences }}</p>
                <p class="text-[9px] sm:text-[10px] font-bold text-rose-600/70 uppercase tracking-wider mt-0.5">Total Alpha</p>
            </div>
        </div>
        <p class="sm:hidden mt-1 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">← Geser →</p>
    </div>

    {{-- Monthly Breakdown --}}
    @if($monthlyData->count() > 0)
    <div>
        <div class="flex items-center justify-between mb-3 px-1">
            <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest">Ringkasan Per Bulan</h3>
            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-lg border border-indigo-100">{{ $monthlyData->count() }} Bulan</span>
        </div>

        <div class="space-y-2.5">
            @foreach($monthlyData as $month)
                @php
                    $total = $month->total;
                    $present = $month->present;
                    $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
                    $barColor = $rate >= 90 ? 'bg-emerald-500' : ($rate >= 75 ? 'bg-amber-500' : 'bg-rose-500');
                    $textColor = $rate >= 90 ? 'text-emerald-600' : ($rate >= 75 ? 'text-amber-600' : 'text-rose-600');
                    $monthKey = \Carbon\Carbon::parse($month->date)->format('Y-m');
                @endphp
                <div x-data="{ open: false }" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <button type="button" @click="open = !open"
                            class="w-full px-4 py-3.5 flex items-center gap-3 text-left active:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 bg-gray-100 rounded-xl flex flex-col items-center justify-center shrink-0">
                            <span class="text-[7px] font-black text-gray-400 uppercase leading-none">{{ \Carbon\Carbon::parse($month->date)->translatedFormat('M') }}</span>
                            <span class="text-[10px] font-black text-gray-700 leading-none mt-0.5">{{ \Carbon\Carbon::parse($month->date)->format('y') }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-black text-gray-900">{{ \Carbon\Carbon::parse($month->date)->translatedFormat('F Y') }}</p>
                            <div class="mt-1.5 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div class="{{ $barColor }} h-full rounded-full transition-all duration-500" style="width: {{ $rate }}%"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-sm font-black {{ $textColor }}">{{ $rate }}%</span>
                            <svg class="w-4 h-4 text-gray-300 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>

                    <div x-show="open" x-collapse x-cloak class="px-4 pb-4 border-t border-gray-50">
                        <div class="pt-3 grid grid-cols-4 gap-2">
                            <div class="bg-emerald-50 rounded-xl p-2.5 text-center border border-emerald-100">
                                <p class="text-[8px] font-black text-emerald-500 uppercase">Hadir</p>
                                <p class="text-sm font-black text-emerald-700 mt-0.5">{{ $present }}</p>
                            </div>
                            <div class="bg-orange-50 rounded-xl p-2.5 text-center border border-orange-100">
                                <p class="text-[8px] font-black text-orange-500 uppercase">Sakit</p>
                                <p class="text-sm font-black text-orange-700 mt-0.5">{{ $month->sick }}</p>
                            </div>
                            <div class="bg-sky-50 rounded-xl p-2.5 text-center border border-sky-100">
                                <p class="text-[8px] font-black text-sky-500 uppercase">Izin</p>
                                <p class="text-sm font-black text-sky-700 mt-0.5">{{ $month->excused }}</p>
                            </div>
                            <div class="bg-rose-50 rounded-xl p-2.5 text-center border border-rose-100">
                                <p class="text-[8px] font-black text-rose-500 uppercase">Alpha</p>
                                <p class="text-sm font-black text-rose-700 mt-0.5">{{ $month->absent }}</p>
                            </div>
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-[10px] font-bold text-gray-400">
                            <span>Total catatan: {{ $total }}</span>
                            <span>{{ $present }} dari {{ $total }} hari hadir</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Export Section --}}
    <div>
        <div class="flex items-center justify-between mb-3 px-1">
            <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest">Export Data</h3>
        </div>

        <div class="space-y-3">
            {{-- Excel Export --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-visible">
                <div class="p-4 sm:p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-black text-gray-900">Export Excel</h4>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Spreadsheet detail per siswa</p>
                        </div>
                        <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                    </div>
                    <form action="{{ route('wali-kelas.export.attendance') }}" method="GET" class="flex items-end gap-2"
                          x-data="{ open: false, selected: '', label: 'Pilih Periode' }"
                          @click.outside="open = false">
                        @if($selectedWaliContextKey ?? null)
                            <input type="hidden" name="wali_context" value="{{ $selectedWaliContextKey }}">
                        @endif
                        <div class="flex-1">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Periode</label>
                            <input type="hidden" name="month" :value="selected">
                            <div class="relative">
                                <button type="button" @click="open = !open"
                                        class="w-full min-h-10.5 px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-left text-xs font-bold text-gray-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all shadow-inner flex items-center gap-2">
                                    <span class="flex-1 truncate" :class="selected ? 'text-gray-800' : 'text-gray-400'" x-text="label"></span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="open" x-cloak
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 -translate-y-1"
                                     class="absolute z-30 mt-2 w-full max-h-56 overflow-y-auto bg-white rounded-xl border border-gray-100 shadow-xl shadow-gray-200/70 p-1">
                                    @foreach($months as $val => $monthLabel)
                                        <button type="button"
                                                @click="selected = @js($val); label = @js($monthLabel); open = false"
                                                class="w-full px-3 py-2.5 rounded-lg text-left text-xs font-bold text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                                            {{ $monthLabel }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-emerald-700 active:scale-95 transition-all shadow-lg shadow-emerald-200/70 whitespace-nowrap">
                            Unduh
                        </button>
                    </form>
                </div>
            </div>

            {{-- PDF Export --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-visible">
                <div class="p-4 sm:p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-black text-gray-900">Export PDF</h4>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Dokumen resmi siap cetak</p>
                        </div>
                        <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>
                    <form action="{{ route('wali-kelas.export.attendance.pdf') }}" method="GET" class="flex items-end gap-2"
                          x-data="{ open: false, selected: '', label: 'Pilih Periode' }"
                          @click.outside="open = false">
                        @if($selectedWaliContextKey ?? null)
                            <input type="hidden" name="wali_context" value="{{ $selectedWaliContextKey }}">
                        @endif
                        <div class="flex-1">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Periode</label>
                            <input type="hidden" name="month" :value="selected">
                            <div class="relative">
                                <button type="button" @click="open = !open"
                                        class="w-full min-h-10.5 px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-left text-xs font-bold text-gray-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all shadow-inner flex items-center gap-2">
                                    <span class="flex-1 truncate" :class="selected ? 'text-gray-800' : 'text-gray-400'" x-text="label"></span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="open" x-cloak
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 -translate-y-1"
                                     class="absolute z-30 mt-2 w-full max-h-56 overflow-y-auto bg-white rounded-xl border border-gray-100 shadow-xl shadow-gray-200/70 p-1">
                                    @foreach($months as $val => $monthLabel)
                                        <button type="button"
                                                @click="selected = @js($val); label = @js($monthLabel); open = false"
                                                class="w-full px-3 py-2.5 rounded-lg text-left text-xs font-bold text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                                            {{ $monthLabel }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-700 active:scale-95 transition-all shadow-lg shadow-indigo-200/70 whitespace-nowrap">
                            Unduh
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Info Tips --}}
    <div class="bg-linear-to-br from-blue-50 to-indigo-50 rounded-2xl p-4 sm:p-5 border border-blue-100 relative overflow-hidden">
        <svg class="absolute -right-4 -bottom-4 w-20 h-20 text-blue-500/10" fill="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="relative z-10">
            <h3 class="text-[11px] font-bold text-blue-800 tracking-wide mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Informasi
            </h3>
            <ul class="space-y-1.5 text-xs text-blue-700/90 font-medium">
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Ketuk kartu bulan untuk melihat rincian kehadiran per bulan.
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Gunakan export Excel untuk data detail atau PDF untuk dokumen resmi.
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Persentase kehadiran dihitung dari jumlah hadir + terlambat dari total catatan.
                </li>
            </ul>
        </div>
    </div>
</div>

@else
{{-- No Class State --}}
<div class="space-y-4 sm:space-y-6 pb-24 lg:pb-8">
    <div class="bg-white border border-gray-100 rounded-2xl p-10 sm:p-16 text-center shadow-sm">
        <div class="flex flex-col items-center gap-4">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-50 rounded-2xl flex items-center justify-center border-2 border-dashed border-gray-200">
                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-black text-gray-900 mb-1">Belum Memiliki Kelas</h3>
                <p class="text-xs sm:text-sm text-gray-500 font-medium max-w-sm mx-auto">Anda harus ditugaskan sebagai Wali Kelas terlebih dahulu untuk mengakses rekap semester.</p>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush
