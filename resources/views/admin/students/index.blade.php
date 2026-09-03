{{-- resources/views/admin/students/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Manajemen Siswa')
@section('header', 'Manajemen Siswa')

@section('content')
<div class="space-y-6 pb-8">
    <!-- Header Title Section -->
    <div class="flex flex-col gap-1">
        <h1 class="text-3xl font-black text-gray-900 tracking-tight flex items-center gap-3">
            Data Siswa
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                {{ $students->total() }} Total
            </span>
        </h1>
        <p class="text-sm text-gray-500">
            Kelola data siswa, import/export, dan pantau statistik kehadiran secara real-time.
        </p>
    </div>

    <!-- Tabs for Status Separation -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-6 sm:space-x-8" aria-label="Tabs">
            <a href="{{ route('admin.students.index', ['status' => 'active'] + request()->except('status', 'page')) }}"
               class="{{ $status == 'active' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors">
                <div class="flex items-center gap-2">
                    Siswa Aktif
                    @if($status == 'active')
                        <span class="bg-blue-100 text-blue-600 py-0.5 px-2 rounded-full text-xs">{{ $students->total() }}</span>
                    @endif
                </div>
            </a>
            <a href="{{ route('admin.students.index', ['status' => 'graduated'] + request()->except('status', 'page')) }}"
               class="{{ $status == 'graduated' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors">
                <div class="flex items-center gap-2">
                    Alumni / Lulus
                    @if($status == 'graduated')
                        <span class="bg-indigo-100 text-indigo-600 py-0.5 px-2 rounded-full text-xs">{{ $students->total() }}</span>
                    @endif
                </div>
            </a>
            <a href="{{ route('admin.students.index', ['status' => 'inactive'] + request()->except('status', 'page')) }}"
               class="{{ $status == 'inactive' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors">
                <div class="flex items-center gap-2">
                    Siswa Non-Aktif
                    @if($status == 'inactive')
                        <span class="bg-red-100 text-red-600 py-0.5 px-2 rounded-full text-xs">{{ $students->total() }}</span>
                    @endif
                </div>
            </a>
        </nav>
    </div>

    <!-- Action Toolbar Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <!-- Title Row -->
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-bold text-gray-800">Menu Tindakan & Operasi Data</h3>
                <p class="text-xs text-gray-400 mt-0.5 hidden sm:block">Impor, ekspor, kelola kelulusan, atau tambah data baru di sini</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 sm:gap-3">
            <button onclick="openImportModal()"
                    class="inline-flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold text-gray-600 bg-gray-50 border border-gray-200 rounded-xl hover:bg-white hover:text-gray-900 hover:border-gray-300 transition-all duration-200 shadow-sm">
                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Import Excel
            </button>

            <a href="{{ route('admin.students.export.template') }}"
               class="inline-flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold text-gray-600 bg-gray-50 border border-gray-200 rounded-xl hover:bg-white hover:text-gray-900 hover:border-gray-300 transition-all duration-200 shadow-sm">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                </svg>
                Template
            </a>

            <a href="{{ route('admin.students.export.data', request()->only(['class_id'])) }}"
               class="inline-flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold text-gray-600 bg-gray-50 border border-gray-200 rounded-xl hover:bg-white hover:text-gray-900 hover:border-gray-300 transition-all duration-200 shadow-sm">
                <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                </svg>
                Export Data
            </a>

            <button id="btn-toggle-select" onclick="toggleSelectMode()"
               class="inline-flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold text-gray-600 bg-gray-50 border border-gray-200 rounded-xl hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all duration-200 shadow-sm">
                <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <span id="toggle-select-text">Pilih Massal</span>
            </button>

            <a href="{{ route(request()->segment(1) . '.students.bulk-graduation') }}"
               class="inline-flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold text-gray-600 bg-gray-50 border border-gray-200 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition-all duration-200 shadow-sm">
                <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                Kelulusan
            </a>

            <a href="{{ route('admin.students.create') }}"
               class="col-span-2 sm:col-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-black text-white bg-linear-to-r from-blue-600 to-indigo-600 rounded-xl shadow-md shadow-blue-500/20 hover:from-blue-700 hover:to-indigo-700 transition-all duration-200">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Siswa
            </a>
        </div>
    </div>
    
    <!-- Statistik Cepat -->
    <div class="grid grid-cols-2 gap-4 mb-2">
        <div class="bg-white rounded-2xl shadow-sm p-4 sm:p-5 border border-gray-100 group hover:border-blue-500 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                        @switch($status)
                            @case('graduated') Total Alumni @break
                            @case('inactive') Total Non-Aktif @break
                            @default Total Siswa
                        @endswitch
                    </p>
                    <p class="text-2xl sm:text-3xl font-black text-gray-900 mt-1">{{ $students->total() }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-50 rounded-xl sm:rounded-2xl flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 sm:p-5 border border-gray-100 group hover:border-green-500 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Kelas</p>
                    <p class="text-2xl sm:text-3xl font-black text-gray-900 mt-1">{{ is_countable($classList) ? count($classList) : 0 }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-50 rounded-xl sm:rounded-2xl flex items-center justify-center text-green-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 sm:p-5 border border-gray-100 group hover:border-amber-500 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                        @switch($status)
                            @case('graduated') Lulus Laki-laki @break
                            @case('inactive') Non-Aktif Laki-laki @break
                            @default Laki-laki
                        @endswitch
                    </p>
                    <p class="text-2xl sm:text-3xl font-black text-gray-900 mt-1">{{ $maleCount ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-amber-50 rounded-xl sm:rounded-2xl flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 sm:p-5 border border-gray-100 group hover:border-purple-500 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                        @switch($status)
                            @case('graduated') Lulus Perempuan @break
                            @case('inactive') Non-Aktif Perempuan @break
                            @default Perempuan
                        @endswitch
                    </p>
                    <p class="text-2xl sm:text-3xl font-black text-gray-900 mt-1">{{ $femaleCount ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-50 rounded-xl sm:rounded-2xl flex items-center justify-center text-purple-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-2">
        <form method="GET" action="{{ route('admin.students.index') }}" class="flex flex-col lg:flex-row gap-4">
            <!-- Search Input -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama, NIS, atau NISN siswa..." 
                       class="block w-full pl-12 pr-4 py-3.5 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm">
            </div>

            <!-- Dropdown Filters Grid -->
            <div class="grid grid-cols-2 sm:flex sm:flex-nowrap items-center gap-2 sm:gap-3">
                <div class="relative col-span-2 sm:col-auto sm:w-48">
                    <select name="class_id" onchange="this.form.submit()" 
                            class="appearance-none block w-full pl-4 pr-10 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm">
                        <option value="">Semua Kelas</option>
                        @foreach($classList as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} (TA {{ $class->academic_year }})
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <div class="relative sm:w-32">
                    <select name="grade_level" onchange="this.form.submit()" 
                            class="appearance-none block w-full pl-4 pr-10 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm">
                        <option value="">Tingkat</option>
                        @foreach($gradeLevels as $gl)
                            <option value="{{ $gl }}" {{ request('grade_level') == $gl ? 'selected' : '' }}>{{ $gl }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <div class="relative sm:w-40">
                    <select name="major" onchange="this.form.submit()" 
                            class="appearance-none block w-full pl-4 pr-10 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm">
                        <option value="">Jurusan</option>
                        @foreach($majors as $major)
                            <option value="{{ $major }}" {{ request('major') == $major ? 'selected' : '' }}>{{ $major }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <div class="relative sm:w-32">
                    <select name="gender" onchange="this.form.submit()" 
                            class="appearance-none block w-full pl-4 pr-10 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm">
                        <option value="">Gender</option>
                        <option value="L" {{ request('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ request('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                @if(request()->anyFilled(['search', 'class_id', 'grade_level', 'major', 'gender']))
                    <a href="{{ route('admin.students.index', ['status' => request('status', 'active')]) }}" 
                       class="col-span-2 sm:col-auto inline-flex items-center justify-center gap-2 py-3 px-4 text-sm font-bold text-red-500 bg-red-50 hover:bg-red-100 rounded-xl transition-all"
                       title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span class="sm:hidden">Reset Filter</span>
                    </a>
                @endif
            </div>
        </form>
        
        <!-- Info Hasil Pencarian -->
        @if(request()->anyFilled(['search', 'class_id', 'grade_level', 'major', 'gender']))
            <div class="mt-3 text-sm text-gray-600">
                Menampilkan {{ $students->count() }} dari {{ $students->total() }} siswa
            </div>
        @endif
    </div>
    
    <!-- Tabel Siswa dengan Scroll -->
    <div class="table-container">
        <div class="overflow-x-auto overflow-y-auto custom-scrollbar" style="max-height: calc(100vh - 200px); min-height: 400px;">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="table-header-glass">
                    <tr>
                        <th class="px-4 py-4 w-12 checkbox-column hidden">
                            <input type="checkbox" id="select-all" title="Pilih Semua"
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-24">NIS</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider min-w-[200px]">Nama Siswa</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Gender</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-32">NISN</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider min-w-[150px]">Tempat/Tgl Lahir</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider min-w-[250px]">Alamat Lengkap</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-40">No Telepon</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider min-w-[180px]">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Kelas</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-24">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($students as $student)
                    <tr class="hover:bg-gray-50 transition-colors group">
                        <td class="px-4 py-4 whitespace-nowrap checkbox-column hidden">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                   class="student-checkbox w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-mono font-bold bg-gray-100 text-gray-700 rounded-lg border border-gray-200">
                                {{ $student->nis }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center text-gray-500 text-xs font-bold mr-3">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </div>
                                <div class="text-sm font-bold text-gray-900">{{ $student->name }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($student->gender == 'L')
                                <span class="px-3 py-1 text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100 rounded-lg">Laki-laki</span>
                            @elseif($student->gender == 'P')
                                <span class="px-3 py-1 text-xs font-bold bg-pink-50 text-pink-700 border border-pink-100 rounded-lg">Perempuan</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">
                            {{ $student->nisn ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($student->tempat_lahir || $student->tanggal_lahir)
                                <div class="font-medium text-gray-900">{{ $student->tempat_lahir ?? '-' }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ $student->tanggal_lahir ? \Carbon\Carbon::parse($student->tanggal_lahir)->translatedFormat('d M Y') : '-' }}
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm max-w-xs truncate">
                            <div>{{ $student->address ?? '-' }}</div>
                            @if($student->rt || $student->rw || $student->kelurahan || $student->kecamatan)
                                <div class="text-xs text-gray-400 mt-0.5 font-normal leading-relaxed">
                                    @if($student->rt || $student->rw) RT {{ $student->rt ?? '-' }}/RW {{ $student->rw ?? '-' }}, @endif
                                    @if($student->kelurahan || $student->kecamatan) {{ $student->kelurahan ?? '-' }}, {{ $student->kecamatan ?? '-' }} @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            {{ $student->phone ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 truncate max-w-[150px]">
                            {{ $student->email ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100 rounded-lg">
                                {{ $student->class->name ?? 'Belum ada kelas' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($student->status == 'active')
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-green-50 text-green-700 border border-green-100">
                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-green-500"></span>
                                    Aktif
                                </span>
                            @elseif($student->status == 'graduated')
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-indigo-500"></span>
                                    Lulus
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-700 border border-red-100">
                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-red-500"></span>
                                    Non-Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex space-x-3">
                                <a href="{{ route('admin.students.show', $student) }}" class="text-gray-400 hover:text-blue-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.students.edit', $student) }}" class="text-gray-400 hover:text-green-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus siswa {{ $student->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center text-gray-400">
                                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <p class="text-lg font-medium">Belum ada data siswa</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($students->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-sm text-gray-500 font-medium">
                Menampilkan {{ $students->firstItem() }} - {{ $students->lastItem() }} dari {{ $students->total() }} siswa
            </div>
            <div>
                {{ $students->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Floating Bulk Action Toolbar -->
<div id="bulk-toolbar"
     class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 hidden"
     style="min-width: 340px;">
    <div class="flex items-center gap-4 bg-gray-900 text-white px-6 py-3.5 rounded-2xl shadow-2xl shadow-black/30 border border-white/10 backdrop-blur-md">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 bg-red-500 rounded-full flex items-center justify-center">
                <span id="selected-count-badge" class="text-xs font-black">0</span>
            </div>
            <span class="text-sm font-semibold" id="selected-count-label">siswa dipilih</span>
        </div>
        <div class="h-5 w-px bg-white/20"></div>
        <button type="button" id="btn-bulk-delete"
                class="flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-500 rounded-xl text-sm font-bold transition-all duration-200 hover:scale-105">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            Hapus Terpilih
        </button>
        <button type="button" id="btn-cancel-select"
                class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-xl text-sm font-semibold transition-all duration-200">
            Batal
        </button>
    </div>
</div>

<!-- Hidden Bulk Delete Form -->
<form id="bulk-delete-form" action="{{ route(request()->segment(1) . '.students.bulk-delete') }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
    <div id="bulk-delete-inputs"></div>
</form>

<!-- Modal Import Excel -->
<div id="importModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeImportModal()"></div>
        
        <!-- Modal Panel -->
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all">
            <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900" id="modal-title">
                            Import Data Siswa
                        </h3>
                        <button type="button" onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-lg p-1.5 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="mt-4">
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 transition-all hover:bg-blue-50 cursor-pointer group"
                             onclick="document.getElementById('file').click()">
                            <input type="file" name="file" id="file" class="hidden" accept=".xlsx,.xls,.csv" required>
                            <div class="w-16 h-16 mx-auto bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                            </div>
                            <p class="text-gray-700 font-medium">Klik untuk pilih file atau drag and drop</p>
                            <p class="text-sm text-gray-500 mt-2">Format: .xlsx, .xls, .csv (Max 2MB)</p>
                            <div id="fileName" class="mt-4 text-sm font-semibold text-blue-600 bg-blue-50 py-2 px-4 rounded-lg hidden"></div>
                        </div>

                        <!-- Dropdown Pilihan Kelas Tujuan -->
                        <div class="mt-4">
                            <label for="import_class_id" class="block text-sm font-bold text-gray-700 mb-2">Kelas Tujuan (Opsional)</label>
                            <div class="relative">
                                <select name="class_id" id="import_class_id" 
                                        class="appearance-none block w-full pl-4 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm font-semibold text-gray-700">
                                    <option value="">Pilih Kelas</option>
                                    @foreach($classList as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }} (TA {{ $class->academic_year }})</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-100">
                    <button type="button" onclick="closeImportModal()" 
                            class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-sm transition-colors">
                        Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const selectAll       = document.getElementById('select-all');
    const bulkToolbar     = document.getElementById('bulk-toolbar');
    const selectedBadge   = document.getElementById('selected-count-badge');
    const selectedLabel   = document.getElementById('selected-count-label');
    const btnBulkDelete   = document.getElementById('btn-bulk-delete');
    const btnCancelSelect = document.getElementById('btn-cancel-select');
    const bulkForm        = document.getElementById('bulk-delete-form');
    const bulkInputs      = document.getElementById('bulk-delete-inputs');
    
    let selectModeActive = false;

    function toggleSelectMode() {
        selectModeActive = !selectModeActive;
        const cols = document.querySelectorAll('.checkbox-column');
        const btn = document.getElementById('btn-toggle-select');
        const text = document.getElementById('toggle-select-text');
        
        if (selectModeActive) {
            cols.forEach(el => el.classList.remove('hidden'));
            btn.classList.remove('text-gray-600', 'bg-gray-50', 'border-gray-200');
            btn.classList.add('text-white', 'bg-red-600', 'border-transparent');
            text.textContent = 'Batal Pilih';
        } else {
            cols.forEach(el => el.classList.add('hidden'));
            btn.classList.add('text-gray-600', 'bg-gray-50', 'border-gray-200');
            btn.classList.remove('text-white', 'bg-red-600', 'border-transparent');
            text.textContent = 'Pilih Massal';
            document.querySelectorAll('.student-checkbox').forEach(cb => { cb.checked = false; });
            if (selectAll) selectAll.checked = false;
            bulkToolbar.classList.add('hidden');
        }
    }

    function updateToolbar() {
        const checked = document.querySelectorAll('.student-checkbox:checked');
        const n = checked.length;
        if (n > 0) {
            selectedBadge.textContent  = n;
            bulkToolbar.classList.remove('hidden');
        } else {
            bulkToolbar.classList.add('hidden');
        }
    }

    selectAll?.addEventListener('change', function () {
        document.querySelectorAll('.student-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
        updateToolbar();
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('student-checkbox')) {
            updateToolbar();
        }
    });

    btnCancelSelect?.addEventListener('click', function () {
        document.querySelectorAll('.student-checkbox').forEach(cb => { cb.checked = false; });
        if (selectAll) selectAll.checked = false;
        if (selectModeActive) toggleSelectMode();
        updateToolbar();
    });

    btnBulkDelete?.addEventListener('click', function () {
        const checked = document.querySelectorAll('.student-checkbox:checked');
        if (checked.length === 0) return;
        if (!confirm(`Hapus ${checked.length} siswa?`)) return;
        bulkInputs.innerHTML = '';
        checked.forEach(cb => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = 'student_ids[]';
            inp.value = cb.value;
            bulkInputs.appendChild(inp);
        });
        bulkForm.submit();
    });

    function openImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
    }
    
    function closeImportModal() {
        document.getElementById('importModal').classList.add('hidden');
    }

    // Handle file selection display
    document.getElementById('file').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : '';
        const fileNameDiv = document.getElementById('fileName');
        if (fileName) {
            fileNameDiv.textContent = 'File terpilih: ' + fileName;
            fileNameDiv.classList.remove('hidden');
        } else {
            fileNameDiv.textContent = '';
            fileNameDiv.classList.add('hidden');
        }
    });
</script>
@endpush
@endsection