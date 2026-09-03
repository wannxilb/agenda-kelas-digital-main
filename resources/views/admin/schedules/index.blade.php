{{-- resources/views/admin/schedules/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Manajemen Jadwal')
@section('header', 'Manajemen Jadwal Pelajaran')

@section('content')
<div class="space-y-8 pb-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 mb-6">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Jadwal Pelajaran</h1>
            <p class="mt-2 text-base text-gray-500 max-w-2xl">Kelola jadwal pelajaran per kelas, atur waktu, dan ruangan secara efisien.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 sm:justify-end">
            {{-- Mode penjadwalan: Block vs Normal --}}
            <div class="flex items-center gap-2 border border-gray-200 bg-white rounded-xl px-2 py-1 shadow-sm">
                <span class="pl-1 pr-1 text-xs font-bold text-gray-400 uppercase tracking-wider hidden md:inline">Mode</span>
                <div class="inline-flex rounded-lg bg-gray-100 p-1" id="schedule-mode-toggle">
                    <button type="button" data-mode="block"
                            onclick="setScheduleMode('block')"
                            class="px-3.5 py-1.5 text-xs font-bold rounded-md transition-colors duration-100 active:scale-95 {{ $scheduleMode === 'block' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }} "
                            title="Ganjil/genap bergantian per minggu">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            Block
                        </span>
                    </button>
                    <button type="button" data-mode="normal"
                            onclick="setScheduleMode('normal')"
                            class="px-3.5 py-1.5 text-xs font-bold rounded-md transition-colors duration-100 active:scale-95 {{ $scheduleMode === 'normal' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }} "
                            title="Semua jadwal tampil tiap minggu">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/></svg>
                            Normal
                        </span>
                    </button>
                </div>
            </div>
            {{-- Impor & Ekspor group --}}
            <div class="inline-flex items-center rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden divide-x divide-gray-200">
                <button type="button" id="btn-open-schedule-wizard" onclick="openScheduleWizard()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-50 transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Impor
                </button>
                <a href="{{ route('admin.schedules.export.template') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                    </svg>
                    Ekspor Template
                </a>
            </div>
            {{-- Primary action --}}
            <a href="{{ route('admin.schedules.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-linear-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/20 hover:from-blue-700 hover:to-indigo-700 hover:shadow-xl transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Jadwal
            </a>
        </div>
    </div>

    {{-- Import Modal --}}
    <div id="importModalSchedule" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title-schedule" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeScheduleImport()"></div>
            {{-- Modal Panel --}}
            <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all">
                <form action="{{ route('admin.schedules.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900" id="modal-title-schedule">Import Jadwal Pelajaran</h3>
                            <button type="button" onclick="closeScheduleImport()" class="text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-lg p-1.5 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4">
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-indigo-500 transition-all hover:bg-indigo-50 cursor-pointer group"
                                 onclick="document.getElementById('scheduleFile').click()">
                                <input type="file" name="file" id="scheduleFile" class="hidden" accept=".xlsx,.xls,.csv" required onchange="scheduleFileSelected(this)">
                                <div class="w-16 h-16 mx-auto bg-indigo-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-700 font-medium">Klik untuk pilih file atau drag and drop</p>
                                <p class="text-sm text-gray-500 mt-2">Format: .xlsx, .xls, .csv (Maks 5 MB)</p>
                                <div id="scheduleFileName" class="mt-4 text-sm font-semibold text-indigo-600 bg-indigo-50 py-2 px-4 rounded-lg hidden"></div>
                            </div>

                            <div class="mt-4 text-xs text-gray-500">
                                💡 Unduh <a href="{{ route('admin.schedules.export.template') }}" class="underline font-semibold text-indigo-600">template Excel</a> sebelum mengisi data.
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-100">
                        <button type="button" onclick="closeScheduleImport()"
                                class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-sm transition-colors">
                            Import Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-2">
        <form method="GET" action="{{ route('admin.schedules.index') }}" class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 ml-1">Filter Kelas</label>
                <select name="class_id" class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm font-semibold" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    @foreach($classList as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} ({{ $class->grade_level }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-full lg:w-64">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 ml-1">Filter Hari</label>
                <select name="day" class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm font-semibold" onchange="this.form.submit()">
                    <option value="">Semua Hari</option>
                    <option value="Monday" {{ request('day') == 'Monday' ? 'selected' : '' }}>Senin</option>
                    <option value="Tuesday" {{ request('day') == 'Tuesday' ? 'selected' : '' }}>Selasa</option>
                    <option value="Wednesday" {{ request('day') == 'Wednesday' ? 'selected' : '' }}>Rabu</option>
                    <option value="Thursday" {{ request('day') == 'Thursday' ? 'selected' : '' }}>Kamis</option>
                    <option value="Friday" {{ request('day') == 'Friday' ? 'selected' : '' }}>Jumat</option>
                </select>
            </div>
            @if(request('class_id') || request('day'))
                <div class="flex items-end">
                    <a href="{{ route('admin.schedules.index') }}" class="inline-flex items-center justify-center p-3 text-red-500 hover:bg-red-50 rounded-xl transition-all" title="Reset Filter">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                </div>
            @endif
        </form>
    </div>
    
    <!-- Tampilan Tab (Table / Calendar) -->
    <div x-data="{ activeTab: 'table' }" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gray-50/50 border-b border-gray-100 px-6">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'table'" 
                        :class="{ 'border-blue-600 text-blue-600': activeTab === 'table', 'border-transparent text-gray-500 hover:text-gray-700': activeTab !== 'table' }"
                        class="px-1 py-4 text-sm font-bold border-b-2 transition-all duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Tampilan Tabel
                </button>
                <button @click="activeTab = 'calendar'" 
                        :class="{ 'border-blue-600 text-blue-600': activeTab === 'calendar', 'border-transparent text-gray-500 hover:text-gray-700': activeTab !== 'calendar' }"
                        class="px-1 py-4 text-sm font-bold border-b-2 transition-all duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Tampilan Kalender
                </button>
            </nav>
            <div class="pt-3 pb-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-[10px] font-bold uppercase tracking-wider border-t border-gray-100 mt-2">
                <span class="text-gray-400">Legenda Minggu:</span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full border bg-purple-100 text-purple-800 border-purple-300">Minggu Ganjil</span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full border bg-teal-100 text-teal-800 border-teal-300">Minggu Genap</span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full border bg-gray-100 text-gray-600 border-gray-300">Setiap Minggu</span>
            </div>
        </div>
        
        <!-- Tampilan Tabel (Accordion) -->
        <div x-show="activeTab === 'table'" class="p-6 bg-gray-50/30">
            @if($schedules->count() > 0)
                <div class="space-y-4">
                    @foreach($schedules as $classId => $classSchedules)
                    @php $firstSchedule = $classSchedules->first(); @endphp
                    <div x-data="{ open: false }" class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden transition-all duration-200" :class="{ 'ring-2 ring-blue-500 border-transparent': open }">
                        <!-- Accordion Header -->
                        <button @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between bg-white hover:bg-blue-50/50 transition-colors focus:outline-none">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 flex-shrink-0 rounded-xl bg-blue-100 flex items-center justify-center text-blue-700 font-black text-lg border border-blue-200 shadow-inner">
                                    {{ $firstSchedule->class->grade_level }}
                                </div>
                                <div class="text-left">
                                    <h3 class="text-lg font-bold text-gray-900">{{ $firstSchedule->class->name }}</h3>
                                    <p class="text-xs font-semibold text-blue-600 mt-0.5">{{ $classSchedules->count() }} Jadwal Pelajaran</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="hidden sm:inline-block text-sm font-semibold text-gray-500 transition-colors" :class="{ 'text-blue-600': open }" x-text="open ? 'Tutup Detail' : 'Lihat Detail'"></span>
                                <div class="w-10 h-10 flex-shrink-0 rounded-full flex items-center justify-center shadow-sm border transition-colors duration-200" :class="open ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-50 text-gray-500 border-gray-200'">
                                    <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                    </svg>
                                </div>
                            </div>
                        </button>
                        
                        <!-- Accordion Body (Table) -->
                        <div x-show="open" x-collapse x-cloak>
                            <div class="overflow-x-auto border-t border-gray-100 bg-white">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50/80">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Hari</th>
                                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Guru</th>
                                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Jam & Ruang</th>
                                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @php
                                            $dayOrderMap = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5];
                                            $sortedClassSchedules = $classSchedules->sortBy(function($sch) use ($dayOrderMap) {
                                                return ($dayOrderMap[$sch->day] ?? 99) . '-' . $sch->start_time;
                                            });
                                        @endphp
                                        @foreach($sortedClassSchedules as $schedule)
                                        <tr class="hover:bg-blue-50/40 transition-colors group">
                                            <td class="px-6 py-5 whitespace-nowrap align-middle">
                                                @php
                                                    $dayNames = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => "Jum'at"];
                                                    $dayColors = [
                                                        'Monday' => 'bg-red-50 text-red-700 border-red-200',
                                                        'Tuesday' => 'bg-orange-50 text-orange-700 border-orange-200',
                                                        'Wednesday' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                        'Thursday' => 'bg-green-50 text-green-700 border-green-200',
                                                        'Friday' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                    ];
                                                @endphp
                                                <span class="px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider border rounded-lg shadow-sm {{ $dayColors[$schedule->day] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">
                                                    {{ $dayNames[$schedule->day] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-5 align-middle min-w-[200px]">
                                                <div class="text-sm font-bold text-gray-900 leading-tight">{{ $schedule->subject->name }}</div>
                                                @if($schedule->week_type && $schedule->week_type !== 'semua')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 mt-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider border
                                                        {{ $schedule->week_type == 'ganjil' ? 'bg-purple-100 text-purple-800 border-purple-300' : 'bg-teal-100 text-teal-800 border-teal-300' }}">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                        {{ $schedule->week_type == 'ganjil' ? 'Minggu Ganjil' : 'Minggu Genap' }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 mt-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider border bg-gray-100 text-gray-600 border-gray-300">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        Setiap Minggu
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-5 align-middle whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-9 h-9 flex-shrink-0 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs mr-3">
                                                        {{ strtoupper(substr($schedule->teacher->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-semibold text-gray-900">{{ $schedule->teacher->name }}</div>
                                                        <div class="text-[10px] text-gray-500 mt-0.5">NIP: {{ $schedule->teacher->nip ?? '-' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5 align-middle whitespace-nowrap">
                                                <div class="flex items-center text-sm font-bold text-gray-800 bg-gray-50 border border-gray-200 rounded-lg px-3.5 py-2 inline-flex shadow-sm">
                                                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                </div>
                                                <div class="flex items-center text-[11px] font-medium text-gray-500 mt-2.5 ml-1">
                                                    <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                    </svg>
                                                    Ruang: <span class="text-gray-700 ml-1 font-semibold">{{ $schedule->room_model ? $schedule->room_model->name : ($schedule->room ?? '-') }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5 text-sm whitespace-nowrap align-middle text-left">
                                                <div class="flex items-center justify-start space-x-2">
                                                    <a href="{{ route('admin.schedules.show', $schedule) }}" class="p-2.5 bg-white text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all border border-gray-200 hover:border-blue-200 shadow-sm hover:shadow" title="Detail">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                    </a>
                                                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="p-2.5 bg-white text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-xl transition-all border border-gray-200 hover:border-green-200 shadow-sm hover:shadow" title="Edit">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </a>
                                                    <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="p-2.5 bg-white text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all border border-gray-200 hover:border-red-200 shadow-sm hover:shadow" title="Hapus">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20">
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Belum Ada Jadwal</h3>
                        <p class="text-gray-500 mt-1">Silakan tambahkan jadwal pelajaran untuk kelas.</p>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Tampilan Kalender -->
        <div x-show="activeTab === 'calendar'" class="p-6" x-data="{ openCal: '' }">
            @php
                $dayNames = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => "Jum'at"];
                
                // Time slots dari pengaturan "Jam Pelajaran" (setting schedule_time_slots)
                $timeSlots = \App\Models\Setting::scheduleTimeSlots();
                
                $filteredSchedules = [];
                if(isset($schedules) && $schedules->count() > 0) {
                    foreach($schedules as $classSchedules) {
                        foreach($classSchedules as $schedule) {
                            $classId = $schedule->class_id;
                            if(!isset($filteredSchedules[$classId])) {
                                $filteredSchedules[$classId] = ['class' => $schedule->class, 'schedules' => []];
                            }
                            $filteredSchedules[$classId]['schedules'][] = $schedule;
                        }
                    }
                }
            @endphp
            
            @if(!empty($filteredSchedules))
                <div class="space-y-3">
                @foreach($filteredSchedules as $classId => $classData)
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-visible transition-all duration-200" :class="{ 'ring-2 ring-blue-500 border-transparent': openCal === '{{ $classId }}' }">
                        <button @click="openCal === '{{ $classId }}' ? openCal = '' : openCal = '{{ $classId }}'" class="w-full px-6 py-4 flex items-center justify-between hover:bg-blue-50/50 transition-colors focus:outline-none rounded-t-2xl">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 flex-shrink-0 rounded-xl bg-blue-100 flex items-center justify-center text-blue-700 font-black text-lg border border-blue-200 shadow-inner">
                                    {{ $classData['class']->grade_level }}
                                </div>
                                <div class="text-left">
                                    <h3 class="text-lg font-bold text-gray-900">{{ $classData['class']->name }}</h3>
                                    <p class="text-xs font-semibold text-blue-600 mt-0.5">{{ count($classData['schedules']) }} Jadwal Pelajaran</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="hidden sm:inline-block text-sm font-semibold text-gray-500 transition-colors" :class="{ 'text-blue-600': openCal === '{{ $classId }}' }" x-text="openCal === '{{ $classId }}' ? 'Tutup' : 'Buka Kalender'"></span>
                                <div class="w-10 h-10 flex-shrink-0 rounded-full flex items-center justify-center shadow-sm border transition-colors duration-200" :class="openCal === '{{ $classId }}' ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-50 text-gray-500 border-gray-200'">
                                    <svg x-show="openCal !== '{{ $classId }}'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                    <svg x-show="openCal === '{{ $classId }}'" x-cloak xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                                </div>
                            </div>
                        </button>
                        <div x-show="openCal === '{{ $classId }}'" x-collapse x-cloak>
                            <div class="overflow-x-auto border-t border-gray-100 bg-white rounded-b-2xl">
                                <table class="min-w-full border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="border-b border-r border-gray-100 p-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider sticky left-0 bg-gray-50 z-10" style="min-width: 100px;">Jam Mulai</th>
                                            @foreach($dayNames as $dayKey => $dayName)
                                                <th class="border-b border-gray-100 p-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider" style="min-width: 220px;">{{ $dayName }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($timeSlots as $slot)
                                        <tr>
                                            <td class="border-b border-r border-gray-100 p-4 text-xs font-bold text-gray-700 bg-gray-50/50 sticky left-0 z-10">
                                                {{ $slot['time'] }}
                                                <div class="text-[9px] text-gray-400 font-normal">WIB</div>
                                            </td>
                                            
                                            @if($slot['label'])
                                                <td colspan="5" class="border-b border-gray-100 p-3 bg-gray-50 text-center">
                                                    <div class="flex items-center justify-center space-x-2 text-gray-500 py-4">
                                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        <span class="text-xs font-bold uppercase tracking-[0.2em]">{{ $slot['label'] }}</span>
                                                    </div>
                                                </td>
                                            @else
                                                @foreach($dayNames as $dayKey => $dayName)
                                                    <td class="border-b border-gray-100 p-3 align-top bg-white" style="min-height: 120px;">
                                                        @php
                                                            $schedulesAtTime = [];
                                                            foreach($classData['schedules'] as $schedule) {
                                                                $startTimeStr = \Carbon\Carbon::parse($schedule->start_time)->format('H:i');
                                                                if($schedule->day == $dayKey && $startTimeStr == $slot['time']) { 
                                                                    $schedulesAtTime[] = $schedule; 
                                                                }
                                                            }
                                                        @endphp
                                                        
                                                        @foreach($schedulesAtTime as $schedule)
                                                            <div class="bg-blue-50/80 rounded-xl p-3 mb-2 border border-blue-100 hover:shadow-md transition-all group relative">
                                                                <div class="flex justify-between items-start mb-1">
                                                                    <p class="font-bold text-blue-900 text-xs leading-tight">{{ $schedule->subject->name }}</p>
                                                                </div>
                                                                @if($schedule->week_type && $schedule->week_type !== 'semua')
                                                                    <div class="mb-1">
                                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider border
                                                                            {{ $schedule->week_type == 'ganjil' ? 'bg-purple-100 text-purple-800 border-purple-300' : 'bg-teal-100 text-teal-800 border-teal-300' }}">
                                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                                            {{ $schedule->week_type == 'ganjil' ? 'Minggu Ganjil' : 'Minggu Genap' }}
                                                                        </span>
                                                                    </div>
                                                                @else
                                                                    <div class="mb-1">
                                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider border bg-gray-100 text-gray-600 border-gray-300">
                                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                                            Setiap Minggu
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                                <p class="text-[10px] text-blue-700 font-medium mb-2">
                                                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                                </p>
                                                                <div class="text-[9px] text-blue-600/70 space-y-0.5">
                                                                    <div class="flex items-center">
                                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                                        {{ $schedule->teacher->name }}
                                                                    </div>
                                                                    @if($schedule->room_model || $schedule->room)
                                                                    <div class="flex items-center">
                                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                                                        {{ $schedule->room_model ? $schedule->room_model->name : $schedule->room }}
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                                <div class="absolute top-2 right-2 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="p-1 bg-white rounded-lg shadow-sm text-green-600 hover:bg-green-50">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </td>
                                                @endforeach
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            @else
                <div class="text-center py-20 text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p>Belum ada data jadwal untuk ditampilkan di kalender</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    var scheduleModeCurrent = {!! ($scheduleMode === 'normal' ? "'normal'" : "'block'") !!};

    function showScheduleToast(message, type) {
        var existing = document.getElementById('schedule-toast');
        if (existing) existing.remove();

        var toast = document.createElement('div');
        toast.id = 'schedule-toast';
        var icon = type === 'error'
            ? '<svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            : '<svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        toast.className = 'fixed bottom-5 right-5 z-[200] flex items-start gap-3 max-w-sm w-full bg-white rounded-2xl shadow-2xl border border-gray-100 px-5 py-4 transition-all duration-300';
        toast.innerHTML = icon +
            '<div class="flex-1 min-w-0">' + message + '</div>' +
            '<button type="button" onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600 ml-1 flex-shrink-0">' +
            '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>';
        document.body.appendChild(toast);

        setTimeout(function () {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 4000);
    }

    function applyScheduleModeActive(mode) {
        document.querySelectorAll('#schedule-mode-toggle button').forEach(function (btn) {
            var active = btn.dataset.mode === mode;

            btn.classList.remove('bg-indigo-600', 'text-white', 'shadow-sm');
            btn.classList.remove('text-gray-500', 'text-indigo-700', 'text-indigo-600', 'hover:text-gray-700');

            if (active) {
                btn.classList.add('bg-indigo-600', 'text-white', 'shadow-sm');
            } else {
                btn.classList.add('text-gray-500', 'hover:text-gray-700');
            }
        });
    }

    function scheduleModeMessage(mode) {
        if (mode === 'block') {
            return '<p class="text-sm font-bold text-gray-900">Mode Block aktif</p>' +
                   '<p class="text-xs text-gray-500 mt-0.5">Jadwal ganjil/genap tampil <b>bergantian per minggu</b> sesuai pekan berjalan.</p>'
        }
        return '<p class="text-sm font-bold text-gray-900">Mode Normal aktif</p>' +
               '<p class="text-xs text-gray-500 mt-0.5">Semua jadwal (ganjil + genap) tampil <b>setiap minggu</b> tanpa dibedakan.</p>'
    }

    function setScheduleMode(mode) {
        if (mode === scheduleModeCurrent) return;

        // Optimistic: ubah UI dulu sebelum server merespons
        var previous = scheduleModeCurrent;
        scheduleModeCurrent = mode;
        applyScheduleModeActive(mode);

        fetch('{{ route('admin.schedules.mode') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ mode: mode })
        }).then(function (r) {
            return r.json();
        }).then(function () {
            showScheduleToast(scheduleModeMessage(mode), 'success');
        }).catch(function () {
            scheduleModeCurrent = previous;
            applyScheduleModeActive(previous);
            showScheduleToast('<p class="text-sm font-bold text-gray-900">Gagal mengubah mode</p><p class="text-xs text-gray-500 mt-0.5">Coba lagi beberapa saat.</p>', 'error');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const openBtn = document.getElementById('btn-open-schedule-wizard');
        if (openBtn) openBtn.addEventListener('click', openScheduleWizard);
        // Smart Polling for Schedules
        async function pollSchedules() {
            try {
                const response = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store'
                });
                const html = await response.text();
                
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const newTable = doc.querySelector('table');
                const oldTable = document.querySelector('table');
                if (newTable && oldTable && newTable.innerHTML !== oldTable.innerHTML) {
                    oldTable.style.opacity = '0.5';
                    setTimeout(() => {
                        oldTable.innerHTML = newTable.innerHTML;
                        oldTable.style.opacity = '1';
                        oldTable.closest('.overflow-x-auto').classList.add('bg-blue-50');
                        setTimeout(() => oldTable.closest('.overflow-x-auto').classList.remove('bg-blue-50'), 1000);
                    }, 300);
                }

                const newCalendar = doc.querySelector('[x-show="activeTab === \'calendar\'"]');
                const oldCalendar = document.querySelector('[x-show="activeTab === \'calendar\'"]');
                if (newCalendar && oldCalendar && newCalendar.innerHTML !== oldCalendar.innerHTML) {
                    oldCalendar.innerHTML = newCalendar.innerHTML;
                }
            } catch (e) {}
        }
        // Smart polling: no overlap + backoff + pause when tab hidden.
        poll(pollSchedules, { interval: 30000, backoffMax: 120000 });
    });

    // ===== Import Modal (Jadwal) =====
    function openScheduleWizard() {
        document.getElementById('importModalSchedule').classList.remove('hidden');
    }
    function closeScheduleImport() {
        document.getElementById('importModalSchedule').classList.add('hidden');
    }

    function scheduleFileSelected(input) {
        const fileName = input.files[0] ? input.files[0].name : '';
        const fileNameDiv = document.getElementById('scheduleFileName');
        if (fileName) {
            fileNameDiv.textContent = 'File terpilih: ' + fileName;
            fileNameDiv.classList.remove('hidden');
        } else {
            fileNameDiv.textContent = '';
            fileNameDiv.classList.add('hidden');
        }
    }
</script>
@endpush
@endsection