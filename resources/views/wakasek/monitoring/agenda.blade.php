{{-- resources/views/wakasek/monitoring/agenda.blade.php --}}
@extends('layouts.wakasek')

@section('title', 'Monitoring Agenda')
@section('header', 'Monitoring Agenda')

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
    .anim-card { animation: fadeSlideUp 0.4s ease-out both; }
    .anim-card:nth-child(1) { animation-delay: 0.03s; }
    .anim-card:nth-child(2) { animation-delay: 0.06s; }
    .anim-card:nth-child(3) { animation-delay: 0.09s; }
    .anim-card:nth-child(4) { animation-delay: 0.12s; }
    .anim-card:nth-child(5) { animation-delay: 0.15s; }
    .pulse-dot { animation: pulse-dot 1.5s ease-in-out infinite; }
</style>
@endpush

@section('content')
@php
    use Carbon\Carbon;
    $currentDate = Carbon::parse($date);
    $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
    $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');
    $isToday = $currentDate->isToday();
    $now = Carbon::now();
    $agendaMatchesSchedule = function ($agenda, $schedule) {
        if (!empty($agenda->schedule_id)) {
            return (int) $agenda->schedule_id === (int) $schedule->id;
        }

        return (int) $agenda->subject_id === (int) $schedule->subject_id
            && (int) $agenda->teacher_id === (int) $schedule->teacher_id;
    };

    $totalKelas = $classes->count();
    $totalJamkos = 0;
    $totalBelumTerisi = 0;
    $totalLengkap = 0;
    $totalTanpaJadwal = 0;

    foreach ($classes as $c) {
        if ($c->schedules->isEmpty()) {
            $totalTanpaJadwal++;
            continue;
        }

        $pastFilled = 0;
        $pastEmpty = 0;
        foreach ($c->schedules as $s) {
            $slotEnd = $s->end_time ? Carbon::parse($date . ' ' . $s->end_time) : null;
            $isPast = $slotEnd && $slotEnd->lt($now);

            if (!$isPast) continue;

            $hasAgenda = $c->agendas->contains(fn ($a) => $agendaMatchesSchedule($a, $s));
            $hasStatus = isset($teacherStatuses[$s->teacher_id]);

            if ($hasAgenda || $hasStatus) {
                $pastFilled++;
            } else {
                $pastEmpty++;
            }
        }

        $totalPast = $pastFilled + $pastEmpty;
        if ($totalPast === 0) {
            $totalLengkap++;
        } elseif ($pastEmpty === 0) {
            $totalLengkap++;
        } elseif ($pastFilled === 0) {
            $totalJamkos++;
        } else {
            $totalBelumTerisi++;
        }
    }
@endphp

<div
    x-data="{
        search: '',
        status: 'all',
        visibleCount: {{ $classes->count() }},
        totalCount: {{ $classes->count() }},
        updateCount() {
            this.$nextTick(() => {
                const rows = this.$refs.rows.querySelectorAll('[data-row]');
                let visible = 0;
                rows.forEach(r => { if (r.offsetParent !== null) visible++; });
                this.visibleCount = visible;
            });
        }
    }"
    x-init="updateCount()"
    class="space-y-4 sm:space-y-6 pb-28 md:pb-0"
>

    {{-- Header --}}
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-50"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h1 class="text-lg sm:text-xl font-black text-gray-900 tracking-tight">Monitoring Agenda</h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">{{ $currentDate->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>
            <form method="GET" action="{{ route('wakasek.monitoring.agenda') }}" class="flex items-center gap-2">
                <a href="{{ route('wakasek.monitoring.agenda', ['date' => $prevDate]) }}"
                    class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition-all"
                    title="Hari sebelumnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.requestSubmit()"
                    class="w-36 sm:w-40 h-9 px-3 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500">
                <a href="{{ route('wakasek.monitoring.agenda', ['date' => $nextDate]) }}"
                    class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition-all"
                    title="Hari berikutnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @unless($isToday)
                    <a href="{{ route('wakasek.monitoring.agenda') }}"
                        class="h-9 px-2.5 sm:px-3.5 flex items-center gap-1 bg-indigo-50 text-indigo-700 rounded-xl text-[10px] sm:text-xs font-bold border border-indigo-100 hover:bg-indigo-100 transition-all whitespace-nowrap">
                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Hari Ini
                    </a>
                @endunless
            </form>
        </div>
    </div>

    {{-- Search + Filter --}}
    <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            {{-- Search --}}
            <div class="relative flex-1">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                <input type="text" x-model="search" @input="updateCount()" placeholder="Cari kelas..."
                    class="w-full h-10 pl-10 pr-4 bg-gray-50 border-none rounded-xl text-sm font-semibold text-gray-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-inner placeholder:text-gray-400">
            </div>
            {{-- Result count --}}
            <span class="text-xs text-gray-400 shrink-0" x-show="search !== '' || status !== 'all'" x-cloak>
                Menampilkan <span class="font-semibold text-gray-700" x-text="visibleCount"></span> dari {{ $totalKelas }} kelas
            </span>
        </div>

        {{-- Filter chips --}}
        <div class="mt-4 flex items-center gap-2 overflow-x-auto scrollbar-hide sm:flex-wrap -mx-1 px-1">
            <button @click="status='all'; updateCount()"
                :class="status==='all' ? 'bg-gray-900 text-white shadow-md shadow-gray-900/20' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300'"
                class="shrink-0 px-4 py-2 rounded-xl text-xs font-bold border transition-all duration-200">
                Semua
                <span class="ml-1.5 opacity-70" x-text="totalCount"></span>
            </button>
            <button @click="status='lengkap'; updateCount()"
                :class="status==='lengkap' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20 border-emerald-600' : 'bg-white text-emerald-700 border-emerald-200 hover:bg-emerald-50 hover:border-emerald-300'"
                class="shrink-0 px-4 py-2 rounded-xl text-xs font-bold border transition-all duration-200">
                <span class="w-1.5 h-1.5 rounded-full inline-block mr-1.5 bg-emerald-500"></span>
                Lengkap ({{ $totalLengkap }})
            </button>
            <button @click="status='belum_terisi'; updateCount()"
                :class="status==='belum_terisi' ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20 border-amber-500' : 'bg-white text-amber-700 border-amber-200 hover:bg-amber-50 hover:border-amber-300'"
                class="shrink-0 px-4 py-2 rounded-xl text-xs font-bold border transition-all duration-200">
                <span class="w-1.5 h-1.5 rounded-full inline-block mr-1.5 bg-amber-400"></span>
                Belum ({{ $totalBelumTerisi }})
            </button>
            <button @click="status='jamkos'; updateCount()"
                :class="status==='jamkos' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/20 border-rose-600' : 'bg-white text-rose-700 border-rose-200 hover:bg-rose-50 hover:border-rose-300'"
                class="shrink-0 px-4 py-2 rounded-xl text-xs font-bold border transition-all duration-200">
                <span class="w-1.5 h-1.5 rounded-full inline-block mr-1.5 bg-rose-500"></span>
                Jamkos ({{ $totalJamkos }})
            </button>
            <button @click="status='kosong'; updateCount()"
                :class="status==='kosong' ? 'bg-gray-600 text-white shadow-md shadow-gray-600/20 border-gray-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300'"
                class="shrink-0 px-4 py-2 rounded-xl text-xs font-bold border transition-all duration-200">
                Tanpa Jadwal ({{ $totalTanpaJadwal }})
            </button>
        </div>
    </div>

    {{-- Summary Stats Cards --}}
    <div class="relative -mx-4 sm:mx-0">
        <div class="flex sm:grid sm:grid-cols-5 gap-3 sm:gap-3 overflow-x-auto scrollbar-hide snap-x snap-mandatory pb-3 sm:pb-0 sm:overflow-visible px-4 sm:px-0">
            <button @click="status='all'; updateCount()"
                :class="status==='all' ? 'border-indigo-200 bg-indigo-50/50 shadow-md shadow-indigo-100/60' : 'border-gray-100 bg-white'"
                class="anim-card snap-center shrink-0 w-[140px] sm:w-auto rounded-2xl shadow-sm border p-3.5 sm:p-4 group hover:border-indigo-400 transition-all duration-300 text-left sm:text-center">
                <div class="flex sm:flex-col items-center sm:items-center gap-3 sm:gap-0">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform duration-300 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0 sm:mt-1.5">
                        <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Total</p>
                        <p class="text-lg sm:text-2xl lg:text-3xl font-black text-gray-900 -mt-0.5">{{ $totalKelas }}</p>
                    </div>
                </div>
            </button>
            <button @click="status='lengkap'; updateCount()"
                :class="status==='lengkap' ? 'border-emerald-200 bg-emerald-50/50 shadow-md shadow-emerald-100/60' : 'border-gray-100 bg-white'"
                class="anim-card snap-center shrink-0 w-[140px] sm:w-auto rounded-2xl shadow-sm border p-3.5 sm:p-4 group hover:border-emerald-400 transition-all duration-300 text-left sm:text-center">
                <div class="flex sm:flex-col items-center sm:items-center gap-3 sm:gap-0">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform duration-300 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0 sm:mt-1.5">
                        <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Lengkap</p>
                        <p class="text-lg sm:text-2xl lg:text-3xl font-black text-emerald-600 -mt-0.5">{{ $totalLengkap }}</p>
                    </div>
                </div>
            </button>
            <button @click="status='belum_terisi'; updateCount()"
                :class="status==='belum_terisi' ? 'border-amber-200 bg-amber-50/50 shadow-md shadow-amber-100/60' : 'border-gray-100 bg-white'"
                class="anim-card snap-center shrink-0 w-[140px] sm:w-auto rounded-2xl shadow-sm border p-3.5 sm:p-4 group hover:border-amber-400 transition-all duration-300 text-left sm:text-center">
                <div class="flex sm:flex-col items-center sm:items-center gap-3 sm:gap-0">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform duration-300 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0 sm:mt-1.5">
                        <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Belum</p>
                        <p class="text-lg sm:text-2xl lg:text-3xl font-black text-amber-600 -mt-0.5">{{ $totalBelumTerisi }}</p>
                    </div>
                </div>
            </button>
            <button @click="status='jamkos'; updateCount()"
                :class="status==='jamkos' ? 'border-rose-200 bg-rose-50/50 shadow-md shadow-rose-100/60' : 'border-gray-100 bg-white'"
                class="anim-card snap-center shrink-0 w-[140px] sm:w-auto rounded-2xl shadow-sm border p-3.5 sm:p-4 group hover:border-rose-400 transition-all duration-300 text-left sm:text-center">
                <div class="flex sm:flex-col items-center sm:items-center gap-3 sm:gap-0">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600 group-hover:scale-110 transition-transform duration-300 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0 sm:mt-1.5">
                        <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Jamkos</p>
                        <p class="text-lg sm:text-2xl lg:text-3xl font-black text-rose-600 -mt-0.5">{{ $totalJamkos }}</p>
                    </div>
                </div>
            </button>
            <button @click="status='kosong'; updateCount()"
                :class="status==='kosong' ? 'border-gray-300 bg-gray-50 shadow-md shadow-gray-100/60' : 'border-gray-100 bg-white'"
                class="anim-card snap-center shrink-0 w-[140px] sm:w-auto rounded-2xl shadow-sm border p-3.5 sm:p-4 group hover:border-gray-400 transition-all duration-300 text-left sm:text-center">
                <div class="flex sm:flex-col items-center sm:items-center gap-3 sm:gap-0">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 bg-gray-50 rounded-xl flex items-center justify-center text-gray-600 group-hover:scale-110 transition-transform duration-300 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0 sm:mt-1.5">
                        <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Kosong</p>
                        <p class="text-lg sm:text-2xl lg:text-3xl font-black text-gray-900 -mt-0.5">{{ $totalTanpaJadwal }}</p>
                    </div>
                </div>
            </button>
        </div>
    </div>

    {{-- Konten --}}
    <div x-ref="rows">

        {{-- Tabel (desktop) --}}
        <div class="hidden md:block bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200">
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider w-[120px]">Kelas</th>
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Jadwal Hari Ini</th>
                            <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Agenda Terisi</th>
                            <th class="px-5 py-3 text-center text-[11px] font-bold text-gray-400 uppercase tracking-wider w-[90px]">Izin</th>
                            <th class="px-5 py-3 text-center text-[11px] font-bold text-gray-400 uppercase tracking-wider w-[130px]">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classes as $class)
                            @php
                                $belumTerisiCount = 0;
                                $guruIzinCount = 0;
                                $pastFilled = 0;
                                $pastEmpty = 0;
                                foreach ($class->schedules as $schedule) {
                                    $slotEnd = $schedule->end_time ? Carbon::parse($date . ' ' . $schedule->end_time) : null;
                                    $isPast = $slotEnd && $slotEnd->lt($now);

                                    $hasAgenda = $class->agendas->contains(fn ($a) => $agendaMatchesSchedule($a, $schedule));
                                    $hasStatus = isset($teacherStatuses[$schedule->teacher_id]);

                                    if ($hasStatus) $guruIzinCount++;

                                    if ($isPast) {
                                        if ($hasAgenda || $hasStatus) {
                                            $pastFilled++;
                                        } else {
                                            $pastEmpty++;
                                        }
                                    } else {
                                        if (!$hasAgenda && !$hasStatus) {
                                            $belumTerisiCount++;
                                        }
                                    }
                                }

                                $totalPast = $pastFilled + $pastEmpty;
                                if ($class->schedules->isEmpty()) {
                                    $rowStatus = 'kosong';
                                } elseif ($totalPast === 0 || $pastEmpty === 0) {
                                    $rowStatus = 'lengkap';
                                } elseif ($pastFilled === 0) {
                                    $rowStatus = 'jamkos';
                                } else {
                                    $rowStatus = 'belum_terisi';
                                }

                                $totalSlots = $class->schedules->count();
                            @endphp
                            <tr data-row
                                x-show="(status === 'all' || status === '{{ $rowStatus }}') && (search === '' || '{{ Str::lower($class->name) }}'.includes(search.toLowerCase()))"
                                x-transition
                                class="border-b border-gray-100 last:border-0 hover:bg-gray-50/50 transition-colors">

                                {{-- Kelas --}}
                                <td class="px-5 py-4 align-top">
                                    <div class="font-semibold text-gray-900 text-sm">{{ $class->name }}</div>
                                    @if($totalSlots > 0)
                                        <div class="text-[11px] text-gray-400 mt-0.5">{{ $totalSlots }} jadwal hari ini</div>
                                    @endif
                                </td>

                                {{-- Jadwal --}}
                                <td class="px-5 py-4 align-top">
                                    @if($class->schedules->isEmpty())
                                        <span class="text-xs text-gray-400 italic">—</span>
                                    @else
                                        <div class="space-y-1.5">
                                            @foreach($class->schedules as $schedule)
                                                @php
                                                    $hasAgenda = $class->agendas->contains(fn ($a) => $agendaMatchesSchedule($a, $schedule));
                                                    $teacherStatus = $teacherStatuses[$schedule->teacher_id] ?? null;
                                                    $isIzin = $teacherStatus && $teacherStatus->type === 'izin';
                                                    $isSakit = $teacherStatus && $teacherStatus->type === 'sakit';
                                                    $isTugasLuar = $teacherStatus && $teacherStatus->type === 'tugas_luar';
                                                    $slotEnd = $schedule->end_time ? Carbon::parse($date . ' ' . $schedule->end_time) : null;
                                                    $isPast = $slotEnd && $slotEnd->lt($now);
                                                @endphp
                                                <div class="flex items-center gap-2">
                                                    @if($hasAgenda)
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                                                    @elseif($isTugasLuar)
                                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 shrink-0"></span>
                                                    @elseif($isIzin)
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>
                                                    @elseif($isSakit)
                                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-400 shrink-0"></span>
                                                    @elseif($isPast)
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0 animate-pulse"></span>
                                                    @else
                                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300 shrink-0"></span>
                                                    @endif
                                                    <div class="text-xs leading-snug">
                                                        <span class="font-medium text-gray-800">{{ $schedule->subject?->name ?? '?' }}</span>
                                                        <span class="text-gray-300 mx-0.5">·</span>
                                                        <span class="text-gray-500">{{ $schedule->teacher?->name ?? '-' }}</span>
                                                        <span class="text-gray-300 mx-0.5">·</span>
                                                        <span class="text-gray-400">{{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '' }} – {{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '' }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>

                                {{-- Terisi --}}
                                <td class="px-5 py-4 align-top">
                                    @if($class->agendas->isEmpty())
                                        <span class="text-xs text-gray-400 italic">—</span>
                                    @else
                                        <div class="space-y-1.5">
                                            @foreach($class->agendas as $agenda)
                                                <div class="text-xs leading-snug">
                                                    <span class="font-medium text-gray-800">{{ $agenda->subject?->name ?? '?' }}</span>
                                                    <span class="text-gray-300 mx-0.5">·</span>
                                                    <span class="text-gray-500">{{ Str::limit($agenda->title ?? 'Tanpa judul', 30) }}</span>
                                                    <span class="text-gray-400 text-[10px] ml-0.5">({{ $agenda->teacher?->name ?? '-' }})</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>

                                {{-- Izin --}}
                                <td class="px-5 py-4 align-top text-center">
                                    @if($guruIzinCount > 0)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-amber-50 text-amber-700 text-xs font-bold">{{ $guruIzinCount }}</span>
                                    @else
                                        <span class="text-xs text-gray-300">0</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-4 align-top text-center">
                                    @if($rowStatus === 'jamkos')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-700 rounded-lg text-xs font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                            JAMKOS
                                        </span>
                                    @elseif($rowStatus === 'belum_terisi')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-700 rounded-lg text-xs font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            BELUM TERISI ({{ $belumTerisiCount }})
                                        </span>
                                    @elseif($rowStatus === 'kosong')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-500 rounded-lg text-xs font-bold">
                                            TANPA JADWAL
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            LENGKAP
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <p class="text-gray-400 text-sm">Tidak ada data kelas untuk tanggal ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Empty state saat filter tidak cocok --}}
                <div x-show="visibleCount === 0 && {{ $classes->count() }} > 0" x-cloak class="px-6 py-16 text-center">
                    <p class="text-gray-400 text-sm">Tidak ada kelas yang cocok dengan pencarian/filter.</p>
                    <button @click="search=''; status='all'; updateCount()" class="mt-2 text-xs font-semibold text-blue-600 hover:underline">
                        Reset filter
                    </button>
                </div>
            </div>
        </div>

        {{-- Kartu (mobile) --}}
        <div class="md:hidden space-y-3">
            @forelse($classes as $class)
                @php
                    $belumTerisiCount = 0;
                    $guruIzinCount = 0;
                    $pastFilled = 0;
                    $pastEmpty = 0;
                    $filledSlots = 0;
                    $teacherStatusTypeCounts = [
                        'izin' => 0,
                        'sakit' => 0,
                        'tugas_luar' => 0,
                    ];
                    foreach ($class->schedules as $schedule) {
                        $slotEnd = $schedule->end_time ? Carbon::parse($date . ' ' . $schedule->end_time) : null;
                        $isPast = $slotEnd && $slotEnd->lt($now);

                        $hasAgenda = $class->agendas->contains(fn ($a) => $agendaMatchesSchedule($a, $schedule));
                        $teacherStatus = $teacherStatuses[$schedule->teacher_id] ?? null;
                        $hasStatus = (bool) $teacherStatus;
                        $isFilled = $hasAgenda || $hasStatus;

                        if ($hasStatus) {
                            $guruIzinCount++;
                            if (isset($teacherStatusTypeCounts[$teacherStatus->type])) {
                                $teacherStatusTypeCounts[$teacherStatus->type]++;
                            }
                        }
                        if ($isFilled) $filledSlots++;

                        if ($isPast) {
                            if ($isFilled) {
                                $pastFilled++;
                            } else {
                                $pastEmpty++;
                            }
                        } else {
                            if (!$isFilled) {
                                $belumTerisiCount++;
                            }
                        }
                    }

                    $totalPast = $pastFilled + $pastEmpty;
                    if ($class->schedules->isEmpty()) {
                        $rowStatus = 'kosong';
                    } elseif ($totalPast === 0 || $pastEmpty === 0) {
                        $rowStatus = 'lengkap';
                    } elseif ($pastFilled === 0) {
                        $rowStatus = 'jamkos';
                    } else {
                        $rowStatus = 'belum_terisi';
                    }

                    $totalSlots = $class->schedules->count();
                    $progress = $totalSlots > 0 ? ($filledSlots / $totalSlots) * 100 : 0;
                    $statusText = [
                        'jamkos' => 'Jamkos',
                        'belum_terisi' => 'Belum Terisi',
                        'kosong' => 'Tanpa Jadwal',
                        'lengkap' => 'Lengkap',
                    ][$rowStatus] ?? 'Lengkap';
                    $accentBorder = [
                        'jamkos' => 'border-l-rose-500',
                        'belum_terisi' => 'border-l-indigo-500',
                        'kosong' => 'border-l-gray-400',
                        'lengkap' => 'border-l-emerald-500',
                    ][$rowStatus] ?? 'border-l-emerald-500';
                    $statusBadgeBg = [
                        'jamkos' => 'bg-rose-500',
                        'belum_terisi' => 'bg-indigo-500',
                        'kosong' => 'bg-gray-400',
                        'lengkap' => 'bg-emerald-500',
                    ][$rowStatus] ?? 'bg-emerald-500';
                    $statusPillClass = [
                        'jamkos' => 'bg-rose-50 text-rose-700 ring-rose-100',
                        'belum_terisi' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
                        'kosong' => 'bg-gray-100 text-gray-500 ring-gray-200',
                        'lengkap' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                    ][$rowStatus] ?? 'bg-emerald-50 text-emerald-700 ring-emerald-100';
                    $progressBg = [
                        'jamkos' => 'from-rose-500 to-red-500',
                        'belum_terisi' => 'from-indigo-500 to-blue-500',
                        'kosong' => 'from-gray-300 to-gray-400',
                        'lengkap' => 'from-emerald-500 to-teal-500',
                    ][$rowStatus] ?? 'from-emerald-500 to-teal-500';
                    $progressTextClass = [
                        'jamkos' => 'text-rose-600',
                        'belum_terisi' => 'text-indigo-600',
                        'kosong' => 'text-gray-500',
                        'lengkap' => 'text-emerald-600',
                    ][$rowStatus] ?? 'text-emerald-600';
                @endphp
                <div data-row
                    x-data="{ open: false }"
                    x-show="(status === 'all' || status === '{{ $rowStatus }}') && (search === '' || '{{ Str::lower($class->name) }}'.includes(search.toLowerCase()))"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-3"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-3"
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden border-l-4 {{ $accentBorder }} active:scale-[0.98] transition-all duration-200">

                    <button @click="open = !open" class="w-full px-4 py-3.5 text-left">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-black text-white shrink-0 {{ $statusBadgeBg }}">
                                    {{ strtoupper(substr($class->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-gray-900 text-sm block truncate">{{ $class->name }}</span>
                                        <span class="w-1.5 h-1.5 rounded-full {{ $rowStatus === 'jamkos' ? 'bg-rose-500 pulse-dot' : ($rowStatus === 'belum_terisi' ? 'bg-indigo-500' : ($rowStatus === 'kosong' ? 'bg-gray-400' : 'bg-emerald-500')) }} shrink-0"></span>
                                    </div>
                                    <span class="text-[10px] font-medium text-gray-400">{{ $totalSlots }} jadwal · {{ $filledSlots }} terisi</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="px-2 py-1 rounded-lg text-[9px] font-bold ring-1 {{ $statusPillClass }}">{{ $statusText }}</span>
                                <div class="w-6 h-6 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-gray-400 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2.5 flex items-center gap-2">
                            <div class="h-2 flex-1 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-linear-to-r {{ $progressBg }}" style="width: {{ $progress }}%"></div>
                            </div>
                            <span class="text-[9px] font-bold {{ $progressTextClass }}">{{ round($progress) }}%</span>
                        </div>
                        @if($pastEmpty > 0 || $belumTerisiCount > 0 || $guruIzinCount > 0)
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @if($pastEmpty > 0)
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-2 py-1 text-[9px] font-bold text-rose-700 ring-1 ring-rose-100">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>{{ $pastEmpty }} perlu agenda
                                    </span>
                                @endif
                                @if($belumTerisiCount > 0)
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-2 py-1 text-[9px] font-bold text-gray-600 ring-1 ring-gray-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>{{ $belumTerisiCount }} belum mulai
                                    </span>
                                @endif
                                @if($teacherStatusTypeCounts['izin'] > 0)
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2 py-1 text-[9px] font-bold text-amber-700 ring-1 ring-amber-100">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>{{ $teacherStatusTypeCounts['izin'] }} guru izin
                                    </span>
                                @endif
                                @if($teacherStatusTypeCounts['sakit'] > 0)
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-orange-50 px-2 py-1 text-[9px] font-bold text-orange-700 ring-1 ring-orange-100">
                                        <span class="h-1.5 w-1.5 rounded-full bg-orange-400"></span>{{ $teacherStatusTypeCounts['sakit'] }} guru sakit
                                    </span>
                                @endif
                                @if($teacherStatusTypeCounts['tugas_luar'] > 0)
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-sky-50 px-2 py-1 text-[9px] font-bold text-sky-700 ring-1 ring-sky-100">
                                        <span class="h-1.5 w-1.5 rounded-full bg-sky-400"></span>{{ $teacherStatusTypeCounts['tugas_luar'] }} tugas luar
                                    </span>
                                @endif
                            </div>
                        @endif
                    </button>

                    {{-- Expanded content --}}
                    <div x-show="open" x-collapse.duration.300ms>
                        <div class="px-4 pb-4 border-t border-gray-50 pt-3 space-y-4">
                            {{-- Schedule list --}}
                            <div>
                                <div class="flex items-center gap-1.5 mb-2.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Jadwal Hari Ini</p>
                                </div>
                                <div class="space-y-1.5">
                                @forelse($class->schedules as $schedule)
                                    @php
                                        $hasAgenda = $class->agendas->contains(fn ($a) => $agendaMatchesSchedule($a, $schedule));
                                        $teacherStatus = $teacherStatuses[$schedule->teacher_id] ?? null;
                                        $isIzin = $teacherStatus && $teacherStatus->type === 'izin';
                                        $isSakit = $teacherStatus && $teacherStatus->type === 'sakit';
                                        $isTugasLuar = $teacherStatus && $teacherStatus->type === 'tugas_luar';
                                        $slotEnd = $schedule->end_time ? Carbon::parse($date . ' ' . $schedule->end_time) : null;
                                        $isPast = $slotEnd && $slotEnd->lt($now);
                                        $slotDot = $hasAgenda ? 'bg-emerald-500' : ($isTugasLuar ? 'bg-sky-400' : ($isSakit ? 'bg-orange-400' : ($isIzin ? 'bg-amber-400' : ($isPast ? 'bg-rose-500' : 'bg-gray-300'))));
                                        $slotLabel = $hasAgenda ? 'Agenda terisi' : ($isTugasLuar ? 'Guru tugas luar' : ($isSakit ? 'Guru sakit' : ($isIzin ? 'Guru izin' : ($isPast ? 'Perlu agenda' : 'Belum mulai'))));
                                        $slotLabelClass = $hasAgenda ? 'bg-emerald-100 text-emerald-700 ring-emerald-100' : ($isTugasLuar ? 'bg-sky-100 text-sky-700 ring-sky-100' : ($isSakit ? 'bg-orange-100 text-orange-700 ring-orange-100' : ($isIzin ? 'bg-amber-100 text-amber-700 ring-amber-100' : ($isPast ? 'bg-rose-100 text-rose-700 ring-rose-100' : 'bg-gray-100 text-gray-500 ring-gray-200'))));
                                        $slotCardClass = $hasAgenda ? 'border-emerald-100 bg-emerald-50/35' : ($isTugasLuar ? 'border-sky-100 bg-sky-50/35' : ($isSakit ? 'border-orange-100 bg-orange-50/35' : ($isIzin ? 'border-amber-100 bg-amber-50/35' : ($isPast ? 'border-rose-100 bg-rose-50/35' : 'border-gray-100 bg-gray-50/60'))));
                                    @endphp
                                    <div class="flex items-center gap-2.5 rounded-xl border px-3 py-2.5 {{ $slotCardClass }}">
                                        <span class="w-2 h-2 rounded-full {{ $slotDot }} shrink-0"></span>
                                        <div class="flex-1 min-w-0 text-xs leading-snug">
                                            <span class="font-semibold text-gray-800">{{ $schedule->subject?->name ?? 'N/A' }}</span>
                                            <span class="text-gray-400 mx-1">·</span>
                                            <span class="text-gray-500">{{ $schedule->teacher?->name ?? '-' }}</span>
                                            <span class="text-gray-300 mx-1">·</span>
                                            <span class="text-gray-400">{{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '' }}-{{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '' }}</span>
                                        </div>
                                        <span class="shrink-0 px-2 py-0.5 rounded-md text-[9px] font-bold ring-1 {{ $slotLabelClass }}">{{ $slotLabel }}</span>
                                    </div>
                                @empty
                                    <div class="flex items-center justify-center py-4">
                                        <span class="text-xs text-gray-400 italic">Tidak ada jadwal</span>
                                    </div>
                                @endforelse
                                </div>
                            </div>

                            {{-- Agenda list --}}
                            <div>
                                <div class="flex items-center gap-1.5 mb-2.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Agenda Terisi</p>
                                </div>
                                <div class="space-y-1.5">
                                @forelse($class->agendas as $agenda)
                                    <div class="flex items-center gap-2.5 rounded-xl bg-emerald-50/50 border border-emerald-100 px-3 py-2.5">
                                        <div class="w-6 h-6 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                        <div class="text-xs leading-snug min-w-0">
                                            <span class="font-semibold text-gray-800">{{ $agenda->subject?->name ?? 'N/A' }}</span>
                                            <span class="text-gray-400 mx-1">–</span>
                                            <span class="text-gray-600">{{ Str::limit($agenda->title ?? 'Tanpa Judul', 35) }}</span>
                                            <span class="text-gray-400 text-[10px] ml-0.5">({{ $agenda->teacher?->name ?? '-' }})</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex items-center justify-center py-4">
                                        <svg class="w-8 h-8 text-gray-200 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span class="text-xs text-gray-400 italic">Belum ada agenda terisi</span>
                                    </div>
                                @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-gray-100 px-6 py-16 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <p class="text-sm font-bold text-gray-500">Tidak ada data kelas</p>
                        <p class="text-xs text-gray-400">Pilih tanggal lain untuk melihat agenda</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Empty state saat filter tidak cocok (mobile) --}}
        <div class="md:hidden" x-show="visibleCount === 0 && {{ $classes->count() }} > 0" x-cloak>
            <div class="bg-white rounded-2xl border border-gray-100 px-6 py-14 text-center">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-500">Tidak ada kelas yang cocok</p>
                        <p class="text-xs text-gray-400 mt-1">Coba ubah kata kunci atau filter status</p>
                    </div>
                    <button @click="search=''; status='all'; updateCount()"
                        class="px-5 py-2.5 bg-indigo-50 text-indigo-700 rounded-xl text-xs font-bold border border-indigo-100 hover:bg-indigo-100 transition-all">
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endpush
@endsection
