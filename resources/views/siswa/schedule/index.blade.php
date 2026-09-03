{{-- resources/views/siswa/schedule/index.blade.php --}}
@extends('layouts.siswa')

@section('title', 'Jadwal Pelajaran')
@section('header', 'Jadwal Pelajaran')

@section('content')
<div class="space-y-5">
    {{-- Top Description --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-5">
    <div class="flex items-center justify-between gap-2">
            <a href="{{ route('siswa.schedule.index', ['date' => $currentDate, 'delta' => -1]) }}"
               class="p-2 rounded-lg hover:bg-gray-100 transition-colors text-gray-600 border border-gray-50">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>

            <div class="text-center flex-1">
                <p class="text-[9px] sm:text-[10px] text-gray-400 font-bold uppercase tracking-[0.15em]">Periode Minggu Ini</p>
                <p class="text-[11px] sm:text-sm font-black text-gray-900 mt-0.5">{{ $weekRange }}</p>
                @if($weekTypeLabel)
                    <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider
                        {{ $weekTypeLabel === 'ganjil' ? 'bg-purple-100 text-purple-700' : 'bg-teal-100 text-teal-700' }}">
                        Minggu {{ ucfirst($weekTypeLabel) }}
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-1.5">
                <a href="{{ route('siswa.schedule.index') }}"
                   class="px-3 py-1.5 text-[9px] sm:text-xs font-black uppercase tracking-widest bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all shadow-sm">
                    Hari Ini
                </a>
                
                <a href="{{ route('siswa.schedule.index', ['date' => $currentDate, 'delta' => 1]) }}"
                   class="p-2 rounded-lg hover:bg-gray-100 transition-colors text-gray-600 border border-gray-50">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Daily Schedule List --}}
    <div class="space-y-3.5">

        @foreach($days as $index => $day)
        @php
            $daySchedules = $schedules[$index] ?? collect();
        @endphp

        <div class="bg-white rounded-3xl shadow-sm border overflow-hidden
                    {{ $day['is_today'] ? 'border-blue-200 ring-2 ring-blue-500/5' : 'border-gray-100' }}">

            {{-- Day Header --}}
            <div class="w-full flex items-center justify-between px-4 py-4 sm:px-6">
                <div class="flex items-center space-x-4">
                    {{-- Mini Calendar Flip Design - Scaled Down --}}
                    <div class="relative w-12 h-12 sm:w-14 sm:h-14 shrink-0">
                        <div class="absolute inset-0 bg-white rounded-xl shadow-sm border border-gray-50 overflow-hidden">
                            <div class="h-1/3 {{ $day['is_today'] ? 'bg-blue-600' : 'bg-gray-50' }} flex items-center justify-center">
                                <span class="text-[7px] sm:text-[9px] font-black uppercase tracking-widest {{ $day['is_today'] ? 'text-white' : 'text-gray-400' }}">
                                    {{ $day['month_name'] }}
                                </span>
                            </div>
                            <div class="h-2/3 flex items-center justify-center">
                                <span class="text-lg sm:text-xl font-black {{ $day['is_today'] ? 'text-blue-600' : 'text-gray-900' }}">
                                    {{ $day['day_number'] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="text-left">
                        <div class="flex items-center gap-1.5">
                            <h3 class="text-sm sm:text-lg font-black {{ $day['is_today'] ? 'text-blue-600' : 'text-gray-900' }}">
                                {{ $day['name'] }}
                            </h3>
                            @if($day['is_today'])
                                <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                            @endif
                        </div>
                        <div class="flex items-center mt-0.5">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest leading-none">
                                {{ $daySchedules->count() > 0 ? $daySchedules->count() . ' Pelajaran' : 'Tidak ada jadwal' }}
                            </p>
                        </div>
                    </div>
                </div>

                @if($day['is_today'])
                <div class="hidden sm:block">
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[9px] font-black uppercase tracking-widest border border-blue-50">Hari Ini</span>
                </div>
                @endif
            </div>

            {{-- Day Content --}}
            <div class="bg-gray-50/20">

                @if($daySchedules->count() > 0)
                    <div class="divide-y divide-gray-100/50">
                        @foreach($daySchedules->sortBy('start_time') as $schedule)
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
                        <div class="flex items-center gap-4 px-4 py-4 sm:px-7 hover:bg-white transition-all group relative">
                            {{-- Time Indicator --}}
                            <div class="flex flex-col items-center shrink-0 w-11 sm:w-14">
                                <span class="text-[11px] sm:text-xs font-black text-blue-600">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}</span>
                                <div class="h-4 w-px bg-gray-200 my-0.5"></div>
                                <span class="text-[9px] font-bold text-gray-400">{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</span>
                            </div>

                            {{-- Vertical Line --}}
                            <div class="w-1 h-10 sm:h-12 rounded-full shrink-0 bg-blue-500 shadow-sm shadow-blue-500/10"></div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-0.5 sm:gap-4">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs sm:text-sm font-black text-gray-900 truncate leading-tight group-hover:text-blue-600 transition-colors">
                                            {{ $schedule->subject->name ?? '-' }}
                                        </h4>
                                        @if($schedule->week_type && $schedule->week_type !== 'semua')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold bg-{{ $schedule->week_type == 'ganjil' ? 'purple' : 'teal' }}-100 text-{{ $schedule->week_type == 'ganjil' ? 'purple' : 'teal' }}-700 mt-1">
                                                Minggu {{ ucfirst($schedule->week_type) }}
                                            </span>
                                        @endif
                                        <div class="flex items-center mt-0.5 text-gray-500">
                                            <span class="text-[9px] sm:text-[10px] font-bold opacity-60 truncate">{{ $schedule->teacher->name ?? '-' }}</span>
                                        </div>
                                        @if($teacherStatus)
                                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $statusClass }}">
                                                    {{ $statusLabel }}
                                                </span>
                                                @if($teacherStatus->substituteTeacher)
                                                    <span class="text-[9px] sm:text-[10px] font-bold text-emerald-700 truncate">
                                                        Digantikan {{ $teacherStatus->substituteTeacher->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    @if($schedule->room)
                                    <div class="shrink-0">
                                        <span class="inline-flex items-center text-[9px] font-black text-gray-400 uppercase tracking-tighter">
                                            <svg class="w-2.5 h-2.5 mr-0.5 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                            {{ $schedule->room }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-6 text-center">
                        <p class="text-[9px] font-black text-gray-300 uppercase tracking-widest">Tidak Ada Jadwal</p>
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
