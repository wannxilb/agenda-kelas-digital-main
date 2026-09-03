{{-- resources/views/layouts/walikelas.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $inst = \App\Models\Institution::find(auth()->user()->institution_id ?? null); @endphp
    @if($inst && $inst->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $inst->favicon) }}">
    @endif
    <title>@yield('title') - {{ \App\Models\Setting::get('school_name', 'Agenda Digital') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom); }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 font-['Inter']" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" x-cloak>
    @include('partials.global-skeleton')
    <div class="flex flex-col h-screen overflow-hidden">
        <div class="flex flex-1 overflow-hidden">

        {{-- Sidebar --}}
        <aside
            x-show="sidebarOpen"
            @resize.window="sidebarOpen = window.innerWidth >= 1024"
            x-transition:enter="transition ease-in-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            @click.away="sidebarOpen = window.innerWidth < 1024 ? false : true"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-2xl lg:relative lg:translate-x-0 lg:block">
            <div class="flex flex-col h-full">

                {{-- Logo --}}
                <div class="flex items-center justify-between px-6 py-6 border-b border-gray-100">
                    <div class="flex items-center space-x-3 min-w-0">
                        @if($inst && $inst->logo)
                            <img src="{{ asset('storage/' . $inst->logo) }}" alt="Logo" class="w-10 h-10 rounded-xl object-contain shadow-lg shrink-0">
                        @else
                            <div class="w-10 h-10 bg-linear-to-br from-indigo-600 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <h1 class="text-xl font-bold text-gray-800 truncate">
                                {{ \App\Models\Setting::get('school_name', 'Agenda') }}<span class="text-indigo-600">{{ \App\Models\Setting::get('school_name') ? '' : ' Digital' }}</span>
                            </h1>
                            <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest">Portal Wali Kelas</p>
                        </div>
                    </div>
                    <button @click="sidebarOpen = false" class="text-gray-400 hover:text-gray-600 lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto scrollbar-hide">
                    <div class="mb-4 px-4">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Utama</p>
                    </div>

                    <a href="{{ route('wali-kelas.dashboard') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-all group {{ request()->routeIs('wali-kelas.dashboard') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wali-kelas.dashboard') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span class="ml-3 font-medium">Dashboard</span>
                        @if(request()->routeIs('wali-kelas.dashboard'))
                            <span class="ml-auto w-1.5 h-8 bg-indigo-600 rounded-full"></span>
                        @endif
                    </a>

                    <div class="mt-8 mb-4 px-4">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Monitoring</p>
                    </div>

                    @feature('attendance')
                    <a href="{{ route('wali-kelas.attendance.index') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-all group {{ request()->routeIs('wali-kelas.attendance.index') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wali-kelas.attendance.index') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span class="ml-3 font-medium">Presensi Siswa</span>
                        @if(request()->routeIs('wali-kelas.attendance.index'))
                            <span class="ml-auto w-1.5 h-8 bg-indigo-600 rounded-full"></span>
                        @endif
                    </a>

                    <a href="{{ route('wali-kelas.daily-attendance.index') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-all group {{ request()->routeIs('wali-kelas.daily-attendance.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wali-kelas.daily-attendance.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="ml-3 font-medium">Kehadiran Siswa</span>
                        @if(request()->routeIs('wali-kelas.daily-attendance.*'))
                            <span class="ml-auto w-1.5 h-8 bg-indigo-600 rounded-full"></span>
                        @endif
                    </a>

                    <a href="{{ route('wali-kelas.attendance.report') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-all group {{ request()->routeIs('wali-kelas.attendance.report') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wali-kelas.attendance.report') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="ml-3 font-medium">Laporan Bulanan</span>
                        @if(request()->routeIs('wali-kelas.attendance.report'))
                            <span class="ml-auto w-1.5 h-8 bg-indigo-600 rounded-full"></span>
                        @endif
                    </a>
                    @endfeature

                    @feature('agenda_harian')
                    <a href="{{ route('wali-kelas.agenda.index') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-all group {{ request()->routeIs('wali-kelas.agenda.index') || request()->routeIs('wali-kelas.agenda.show') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wali-kelas.agenda.index') || request()->routeIs('wali-kelas.agenda.show') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="ml-3 font-medium">Agenda</span>
                        @if(request()->routeIs('wali-kelas.agenda.index') || request()->routeIs('wali-kelas.agenda.show'))
                            <span class="ml-auto w-1.5 h-8 bg-indigo-600 rounded-full"></span>
                        @endif
                    </a>

                    <a href="{{ route('wali-kelas.agenda.archive') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-all group {{ request()->routeIs('wali-kelas.agenda.archive') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wali-kelas.agenda.archive') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                        </svg>
                        <span class="ml-3 font-medium">Arsip Agenda</span>
                        @if(request()->routeIs('wali-kelas.agenda.archive'))
                            <span class="ml-auto w-1.5 h-8 bg-indigo-600 rounded-full"></span>
                        @endif
                    </a>
                    @endfeature

                    @feature('nilai_tugas')
                    <a href="{{ route('wali-kelas.grades.index') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-all group {{ request()->routeIs('wali-kelas.grades.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wali-kelas.grades.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m-7 4h8m-8 4h5m-7 6h12a2 2 0 002-2V5a2 2 0 00-2-2H8.5L4 7.5V19a2 2 0 002 2z"></path>
                        </svg>
                        <span class="ml-3 font-medium">Nilai Siswa</span>
                        @if(request()->routeIs('wali-kelas.grades.*'))
                            <span class="ml-auto w-1.5 h-8 bg-indigo-600 rounded-full"></span>
                        @endif
                    </a>
                    @endfeature

                    @feature('export_pdf')
                    <div class="mt-8 mb-4 px-4">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('messages.report') }}</p>
                    </div>

                    <a href="{{ route('wali-kelas.export.attendance') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-all group {{ request()->routeIs('wali-kelas.export.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wali-kelas.export.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span class="ml-3 font-medium">Rekap Semester</span>
                        @if(request()->routeIs('wali-kelas.export.*'))
                            <span class="ml-auto w-1.5 h-8 bg-indigo-600 rounded-full"></span>
                        @endif
                    </a>
                    @endfeature

                    @if(Auth::user()->hasRole('wakasek'))
                    <div class="mt-8 mb-4 px-4">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Akses Portal</p>
                    </div>

                    @if(Auth::user()->hasRole('wakasek'))
                    <a href="{{ route('wakasek.dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-purple-50 hover:text-purple-600 transition-all group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="ml-3 font-medium">Portal Wakasek</span>
                    </a>
                    @endif
                    @endif
                </nav>

                {{-- User Profile --}}
                <div class="p-4 border-t border-gray-100">
                    <div class="flex items-center space-x-3 px-3 py-3 rounded-2xl hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 bg-linear-to-br from-indigo-500 to-blue-500 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-md shrink-0">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-400 font-medium">Wali Kelas</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="Logout" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Mobile Overlay --}}
        <div x-show="sidebarOpen && window.innerWidth < 1024"
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-in-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in-out duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-40 lg:hidden">
        </div>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- Topbar --}}
            <header class="bg-white/80 backdrop-blur-xl border-b border-gray-100 sticky top-0 z-40">
                <div class="px-4 lg:px-8 py-3 lg:py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($inst && $inst->logo)
                                <img src="{{ asset('storage/' . $inst->logo) }}" alt="Logo" class="w-8 h-8 lg:hidden rounded-lg object-contain shrink-0">
                            @else
                                <div class="w-8 h-8 lg:hidden bg-linear-to-br from-indigo-600 to-blue-600 rounded-lg flex items-center justify-center shadow-md shadow-indigo-200/50 shrink-0">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                            @endif
                            <h2 class="text-lg lg:text-xl font-bold text-gray-900 truncate">@yield('header', 'Dashboard')</h2>
                        </div>

                        <div class="flex items-center gap-2 lg:gap-4">
                            <div class="relative" x-data="{ profileOpen: false }">
                                <button @click="profileOpen = !profileOpen" class="focus:outline-none group">
                                    <div class="w-9 h-9 bg-linear-to-br from-indigo-500 to-blue-500 rounded-xl flex items-center justify-center text-white text-xs font-bold shadow-lg shadow-indigo-200/50 group-hover:shadow-indigo-300/50 group-active:scale-95 transition-all">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                </button>

                                {{-- Desktop Dropdown --}}
                                <div x-show="profileOpen" @click.away="profileOpen = false"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="hidden lg:block absolute right-0 mt-3 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden">
                                    <div class="px-5 py-4 bg-linear-to-r from-indigo-50 to-blue-50 border-b border-gray-100">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-11 h-11 bg-linear-to-br from-indigo-500 to-blue-500 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-indigo-200/50">
                                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                                                <p class="text-xs text-indigo-600 font-medium">Wali Kelas</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="py-2">
                                        <a href="{{ route('wali-kelas.profile') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <span>{{ __('messages.my_profile') }}</span>
                                        </a>
                                        @feature('attendance')
                                        <a href="{{ route('wali-kelas.attendance.report') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <span>Laporan Presensi</span>
                                        </a>
                                        @endfeature
                                        <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.247 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                </svg>
                                            </div>
                                            <span>Portal Guru</span>
                                            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="flex items-center gap-3 w-full px-5 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                    </svg>
                                                </div>
                                                <span>{{ __('messages.logout') }}</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                {{-- Mobile Bottom Sheet --}}
                                <template x-teleport="body">
                                    <div x-show="profileOpen" x-cloak
                                         class="lg:hidden fixed inset-0 z-100 flex items-end justify-center"
                                         x-transition:enter="transition ease-out duration-300"
                                         x-transition:enter-start="opacity-0"
                                         x-transition:enter-end="opacity-100"
                                         x-transition:leave="transition ease-in duration-200"
                                         x-transition:leave-start="opacity-100"
                                         x-transition:leave-end="opacity-0">
                                        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="profileOpen = false"></div>
                                        <div class="relative w-full bg-white rounded-t-3xl safe-bottom max-h-[85vh] overflow-y-auto animate-slide-up">
                                            <div class="flex justify-center pt-3 pb-2">
                                                <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
                                            </div>
                                            <div class="px-6 pb-4 flex items-center gap-4 border-b border-gray-100">
                                                <div class="w-14 h-14 bg-linear-to-br from-indigo-500 to-blue-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-indigo-200/50">
                                                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-base font-bold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                                                    <p class="text-sm text-indigo-600 font-medium">Wali Kelas</p>
                                                </div>
                                            </div>
                                            <div class="py-3 px-4 space-y-1">
                                                <a href="{{ route('wali-kelas.profile') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl hover:bg-gray-50 transition-colors">
                                                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-gray-900">{{ __('messages.my_profile') }}</p>
                                                        <p class="text-xs text-gray-500">Lihat dan edit profil</p>
                                                    </div>
                                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </a>
                                                @feature('attendance')
                                                <a href="{{ route('wali-kelas.attendance.report') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl hover:bg-gray-50 transition-colors">
                                                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-gray-900">Laporan Presensi</p>
                                                        <p class="text-xs text-gray-500">Ringkasan presensi kelas</p>
                                                    </div>
                                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </a>
                                                @endfeature
                                                @feature('agenda_harian')
                                                <a href="{{ route('wali-kelas.agenda.archive') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl hover:bg-gray-50 transition-colors">
                                                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-gray-900">Arsip Agenda</p>
                                                        <p class="text-xs text-gray-500">Riwayat agenda kelas binaan</p>
                                                    </div>
                                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </a>
                                                @endfeature
                                                @feature('export_pdf')
                                                <a href="{{ route('wali-kelas.export.attendance') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl hover:bg-gray-50 transition-colors">
                                                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 10.5L12 15m0 0l4.5-4.5M12 15V3"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-gray-900">Rekap</p>
                                                        <p class="text-xs text-gray-500">Export rekap presensi</p>
                                                    </div>
                                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </a>
                                                @endfeature
                                                @if(Auth::user()->hasRole('teacher'))
                                                <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl hover:bg-gray-50 transition-colors">
                                                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.247 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-gray-900">Portal Guru</p>
                                                        <p class="text-xs text-gray-500">Beralih ke portal guru</p>
                                                    </div>
                                                </a>
                                                @endif
                                                @if(Auth::user()->hasRole('wakasek'))
                                                <a href="{{ route('wakasek.dashboard') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl hover:bg-gray-50 transition-colors">
                                                    <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-gray-900">Portal Wakasek</p>
                                                        <p class="text-xs text-gray-500">Beralih ke portal wakasek</p>
                                                    </div>
                                                </a>
                                                @endif
                                                <form method="POST" action="{{ route('logout') }}">
                                                    @csrf
                                                    <button type="submit" class="flex items-center gap-4 w-full px-4 py-3 rounded-2xl hover:bg-red-50 transition-colors">
                                                        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                            </svg>
                                                        </div>
                                                        <div class="flex-1 text-left">
                                                            <p class="text-sm font-semibold text-red-600">{{ __('messages.logout') }}</p>
                                                        </div>
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="px-4 pb-4">
                                                <button @click="profileOpen = false" class="w-full py-3 text-sm font-semibold text-gray-500 bg-gray-100 rounded-2xl hover:bg-gray-200 transition-colors">
                                                    Tutup
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Content Area --}}
            <main class="flex-1 overflow-y-auto scrollbar-hide">
                <div class="px-4 sm:px-6 lg:px-8 @yield('content-padding', 'py-6 sm:py-8 pb-24 lg:pb-8')">
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                            <div class="flex">
                                <svg class="h-5 w-5 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p>
                            </div>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                            <div class="flex">
                                <svg class="h-5 w-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="ml-3 text-sm text-red-700">{{ session('error') }}</p>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>

            {{-- Bottom Navigation --}}
            <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/90 backdrop-blur-xl border-t border-gray-100 safe-bottom">
                <div class="flex items-stretch">
                    @php
                        $navItems = [
                            [
                                'route' => 'wali-kelas.dashboard',
                                'active' => request()->routeIs('wali-kelas.dashboard'),
                                'label' => 'Beranda',
                                'guard' => null,
                                'icon_outline' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>',
                                'icon_filled' => '<path d="M11.47 3.84a.75.75 0 011.06 0l8.99 9a.75.75 0 11-1.06 1.06l-1.03-1.03v7.38a1.5 1.5 0 01-1.5 1.5h-4.5a.75.75 0 01-.75-.75v-3.75a.75.75 0 00-.75-.75h-1.5a.75.75 0 00-.75.75v3.75a.75.75 0 01-.75.75h-4.5a1.5 1.5 0 01-1.5-1.5v-7.38l-1.03 1.03a.75.75 0 01-1.06-1.06l8.99-9z"></path>',
                            ],
                            [
                                'route' => 'wali-kelas.attendance.index',
                                'active' => request()->routeIs('wali-kelas.attendance.index'),
                                'label' => 'Presensi',
                                'guard' => 'attendance',
                                'icon_outline' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                                'icon_filled' => '<path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"></path>',
                            ],
                            [
                                'route' => 'wali-kelas.agenda.index',
                                'active' => request()->routeIs('wali-kelas.agenda.*'),
                                'label' => 'Agenda',
                                'guard' => 'agenda_harian',
                                'icon_outline' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>',
                                'icon_filled' => '<path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0016.5 9h-1.875a1.875 1.875 0 01-1.875-1.875V5.25A3.75 3.75 0 009 1.5H5.625zM7.5 15a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5A.75.75 0 017.5 15zm.75 2.25a.75.75 0 000 1.5H12a.75.75 0 000-1.5H8.25z" clip-rule="evenodd"></path><path d="M12.971 1.816A5.23 5.23 0 0114.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 013.434 1.279 9.768 9.768 0 00-6.963-6.963z"></path>',
                            ],
                            [
                                'route' => 'wali-kelas.daily-attendance.index',
                                'active' => request()->routeIs('wali-kelas.daily-attendance.*'),
                                'label' => 'Kehadiran',
                                'guard' => 'attendance',
                                'icon_outline' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                                'icon_filled' => '<path fill-rule="evenodd" d="M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5zM12.75 6a.75.75 0 00-1.5 0v6c0 .199.079.39.22.53l3 3a.75.75 0 101.06-1.06l-2.78-2.78V6z" clip-rule="evenodd"></path>',
                            ],
                            [
                                'route' => 'wali-kelas.grades.index',
                                'active' => request()->routeIs('wali-kelas.grades.*'),
                                'label' => 'Nilai',
                                'guard' => 'nilai_tugas',
                                'icon_outline' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m-7 4h8m-8 4h5m-7 6h12a2 2 0 002-2V5a2 2 0 00-2-2H8.5L4 7.5V19a2 2 0 002 2z"></path>',
                                'icon_filled' => '<path fill-rule="evenodd" d="M6.75 2.25A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V6.621a2.25 2.25 0 00-.659-1.591l-2.12-2.121a2.25 2.25 0 00-1.592-.659H6.75zM8.25 9a.75.75 0 000 1.5h7.5a.75.75 0 000-1.5h-7.5zm0 3.75a.75.75 0 000 1.5h7.5a.75.75 0 000-1.5h-7.5zm0 3.75a.75.75 0 000 1.5h4.5a.75.75 0 000-1.5h-4.5z" clip-rule="evenodd"></path>',
                            ],
                        ];
                    @endphp

                    @foreach($navItems as $item)
                        @if($item['guard'])
                            @feature($item['guard'])
                                <a href="{{ route($item['route']) }}"
                                   class="flex-1 flex flex-col items-center justify-center py-2 relative {{ $item['active'] ? 'text-indigo-600' : 'text-gray-400' }}">
                                    @if($item['active'])
                                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-indigo-600 rounded-full"></div>
                                    @endif
                                    <div class="relative">
                                        @if($item['active'])
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">{!! $item['icon_filled'] !!}</svg>
                                        @else
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon_outline'] !!}</svg>
                                        @endif
                                    </div>
                                    <span class="text-[10px] mt-0.5 {{ $item['active'] ? 'font-bold' : 'font-medium' }}">{{ $item['label'] }}</span>
                                </a>
                            @endfeature
                        @else
                            <a href="{{ route($item['route']) }}"
                               class="flex-1 flex flex-col items-center justify-center py-2 relative {{ $item['active'] ? 'text-indigo-600' : 'text-gray-400' }}">
                                @if($item['active'])
                                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-indigo-600 rounded-full"></div>
                                @endif
                                <div class="relative">
                                    @if($item['active'])
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">{!! $item['icon_filled'] !!}</svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon_outline'] !!}</svg>
                                    @endif
                                </div>
                                <span class="text-[10px] mt-0.5 {{ $item['active'] ? 'font-bold' : 'font-medium' }}">{{ $item['label'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </nav>
        </div>
    </div>

    @stack('scripts')
    </div>
</body>
</html>
