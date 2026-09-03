{{-- resources/views/layouts/wakasek.blade.php --}}
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
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
    <style>
        html::-webkit-scrollbar, body::-webkit-scrollbar { display: none !important; }
        html, body { -ms-overflow-style: none !important; scrollbar-width: none !important; }
        [x-cloak] { display: none !important; }
        .scrollbar-hide {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
        .scrollbar-hide::-webkit-scrollbar { display: none !important; }
        .ts-wrapper { overflow: visible; }
        .ts-wrapper.single .ts-control,
        .ts-wrapper.multi .ts-control {
            border-radius: 0.75rem !important;
            padding: 0.625rem 1rem !important;
            font-size: 0.875rem !important;
            min-height: 42px !important;
            border: 1px solid #e5e7eb !important;
            box-shadow: none !important;
            background: #f9fafb !important;
        }
        .ts-wrapper.single.focus .ts-control,
        .ts-wrapper.multi.focus .ts-control {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 2px rgba(99,102,241,0.15) !important;
            background: #fff !important;
        }
        .ts-wrapper .ts-dropdown .option.active {
            background: #eef2ff !important;
            color: #4338ca !important;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 font-['Inter']" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" x-cloak>
    @include('partials.global-skeleton')
    <div class="flex flex-col h-screen overflow-hidden">
        <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar -->
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
            class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-2xl lg:relative lg:translate-x-0 lg:block"
            :class="{'hidden': !sidebarOpen && window.innerWidth < 1024, 'lg:block': true}">
            <div class="flex flex-col h-full">
                <!-- Logo Section -->
                <div class="flex items-center justify-between px-6 py-6 border-b border-gray-100">
                    <div class="flex items-center space-x-3">
                        @if($inst && $inst->logo)
                            <img src="{{ asset('storage/' . $inst->logo) }}" alt="Logo" class="w-10 h-10 rounded-xl object-contain shadow-lg">
                        @else
                        <div class="w-10 h-10 bg-linear-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        @endif
                        <div>
                            <h1 class="text-xl font-bold text-gray-800">
                                {{ \App\Models\Setting::get('school_name', 'Agenda') }}<span class="text-blue-600">{{ \App\Models\Setting::get('school_name') ? '' : ' Digital' }}</span>
                            </h1>
                            <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">Wakasek</p>
                        </div>
                    </div>
                    <button @click="sidebarOpen = false" class="text-gray-400 hover:text-gray-600 lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto scrollbar-hide">
                    <div class="mb-4 px-4">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Dashboard</p>
                    </div>

                    <a href="{{ route('wakasek.dashboard') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all group {{ request()->routeIs('wakasek.dashboard') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wakasek.dashboard') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span class="ml-3 font-medium">Dashboard</span>
                        @if(request()->routeIs('wakasek.dashboard'))
                            <span class="ml-auto w-1.5 h-8 bg-blue-600 rounded-full"></span>
                        @endif
                    </a>

                    <div class="mt-8 mb-4 px-4">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Monitoring</p>
                    </div>

                    <a href="{{ route('wakasek.curriculum.index') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all group {{ request()->routeIs('wakasek.curriculum.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wakasek.curriculum.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span class="ml-3 font-medium">Monitoring Kurikulum</span>
                    </a>

                    <a href="{{ route('wakasek.monitoring.agenda') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all group {{ request()->routeIs('wakasek.monitoring.agenda') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wakasek.monitoring.agenda') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="ml-3 font-medium">Monitoring Agenda</span>
                    </a>

                    <a href="{{ route('wakasek.teacher-status.index') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-amber-50 hover:text-amber-600 transition-all group {{ request()->routeIs('wakasek.teacher-status.*') ? 'bg-amber-50 text-amber-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wakasek.teacher-status.*') ? 'text-amber-600' : 'text-gray-400 group-hover:text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="ml-3 font-medium">Izin / Sakit / Tugas Luar</span>
                        @if(request()->routeIs('wakasek.teacher-status.*'))
                            <span class="ml-auto w-1.5 h-8 bg-amber-500 rounded-full"></span>
                        @endif
                    </a>

                    <a href="{{ route('wakasek.teaching.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all group {{ request()->routeIs('wakasek.teaching.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wakasek.teaching.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span class="ml-3 font-medium">Kinerja Mengajar</span>
                    </a>

                    <div class="mt-8 mb-4 px-4">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Evaluasi & {{ __('messages.report') }}</p>
                    </div>

                    <a href="{{ route('wakasek.evaluation.index') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all group {{ request()->routeIs('wakasek.evaluation.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wakasek.evaluation.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="ml-3 font-medium">Evaluasi Akademik</span>
                    </a>

                    @feature('attendance')
                    <a href="{{ route('wakasek.daily-attendance.index') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all group {{ request()->routeIs('wakasek.daily-attendance.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wakasek.daily-attendance.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="ml-3 font-medium">Monitoring Kehadiran</span>
                        @if(request()->routeIs('wakasek.daily-attendance.*'))
                            <span class="ml-auto w-1.5 h-8 bg-blue-600 rounded-full"></span>
                        @endif
                    </a>

                    <a href="{{ route('wakasek.reports.attendance') }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all group {{ request()->routeIs('wakasek.reports.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('wakasek.reports.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="ml-3 font-medium">Laporan Presensi</span>
                    </a>
                    @endfeature

                    @if(Auth::user()->hasRole('wali_kelas'))
                    <div class="mt-8 mb-4 px-4">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Akses Portal</p>
                    </div>
                    @if(Auth::user()->hasRole('wali_kelas'))
                    <a href="{{ route('wali-kelas.dashboard') }}" 
                       class="flex items-center px-4 py-3 text-gray-700 rounded-xl hover:bg-indigo-50 hover:text-indigo-600 transition-all group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="ml-3 font-medium">Portal Wali Kelas</span>
                    </a>
                    @endif
                    @endif
                </nav>

                <!-- User Profile Sidebar Bottom -->
                <div class="p-4 border-t border-gray-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-linear-to-br from-blue-500 to-indigo-500 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-md">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">Wakasek</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Overlay untuk mobile -->
        <div x-show="sidebarOpen && window.innerWidth < 1024" 
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-in-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in-out duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden">
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="bg-white shadow-sm sticky top-0 z-30">
                <div class="px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            @if($inst && $inst->logo)
                                <img src="{{ asset('storage/' . $inst->logo) }}" alt="Logo" class="w-8 h-8 lg:hidden rounded-lg object-contain">
                            @else
                                <div class="w-8 h-8 lg:hidden bg-linear-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center shadow-md">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                            @endif
                            <h2 class="text-xl font-semibold text-gray-800">@yield('header', 'Dashboard')</h2>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <!-- Profile Dropdown -->
                            <div class="relative" x-data="{ profileOpen: false }">
                                <button @click="profileOpen = !profileOpen" class="focus:outline-none group">
                                    <div class="w-9 h-9 bg-linear-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white text-xs font-bold shadow-lg shadow-blue-200/50 group-hover:shadow-blue-300/50 group-active:scale-95 transition-all">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                </button>

                                {{-- Desktop dropdown --}}
                                <div x-show="profileOpen" @click.away="profileOpen = false"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="hidden lg:block absolute right-0 mt-3 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden">
                                    <div class="px-5 py-4 bg-linear-to-r from-blue-50 to-indigo-50 border-b border-gray-100">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-11 h-11 bg-linear-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-blue-200/50">
                                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                                                <p class="text-xs text-blue-600 font-medium">Wakasek</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="py-2">
                                        <a href="{{ route('wakasek.profile') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <span>Profil Saya</span>
                                        </a>
                                        <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                </svg>
                                            </div>
                                            <span>Portal Guru</span>
                                        </a>
                                        @if(Auth::user()->hasRole('wali_kelas'))
                                        <a href="{{ route('wali-kelas.dashboard') }}" class="flex items-center gap-3 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                            </div>
                                            <span>Portal Wali Kelas</span>
                                        </a>
                                        @endif
                                        <div class="mx-5 my-1 border-t border-gray-100"></div>
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

                                {{-- Mobile bottom sheet --}}
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
                                                <div class="w-14 h-14 bg-linear-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-blue-200/50">
                                                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-base font-bold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                                                    <p class="text-sm text-blue-600 font-medium">Wakasek</p>
                                                </div>
                                            </div>
                                            <div class="py-3 px-4 space-y-1">
                                                <a href="{{ route('wakasek.profile') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl hover:bg-gray-50 transition-colors">
                                                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-gray-900">Profil Saya</p>
                                                        <p class="text-xs text-gray-500">Lihat dan edit profil</p>
                                                    </div>
                                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </a>
                                                <a href="{{ route('wakasek.curriculum.index') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl hover:bg-gray-50 transition-colors">
                                                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-gray-900">Monitoring Kurikulum</p>
                                                        <p class="text-xs text-gray-500">Pantau pelaksanaan kurikulum</p>
                                                    </div>
                                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </a>
                                                <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl hover:bg-gray-50 transition-colors">
                                                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-gray-900">Portal Guru</p>
                                                        <p class="text-xs text-gray-500">Beralih ke portal guru</p>
                                                    </div>
                                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </a>
                                                @if(Auth::user()->hasRole('wali_kelas'))
                                                <a href="{{ route('wali-kelas.dashboard') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl hover:bg-gray-50 transition-colors">
                                                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-gray-900">Portal Wali Kelas</p>
                                                        <p class="text-xs text-gray-500">Beralih ke portal wali kelas</p>
                                                    </div>
                                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </a>
                                                @endif
                                                <a href="{{ route('wakasek.reports.attendance') }}" class="flex items-center gap-4 px-4 py-3 rounded-2xl hover:bg-gray-50 transition-colors">
                                                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-gray-900">Laporan Presensi</p>
                                                        <p class="text-xs text-gray-500">Buka filter periode dan rekap absensi</p>
                                                    </div>
                                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </a>
                                                <div class="mx-4 my-1 border-t border-gray-100"></div>
                                                <form method="POST" action="{{ route('logout') }}">
                                                    @csrf
                                                    <button type="submit" class="flex items-center gap-4 w-full px-4 py-3 rounded-2xl text-red-600 hover:bg-red-50 transition-colors">
                                                        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                            </svg>
                                                        </div>
                                                        <div class="flex-1 text-left">
                                                            <p class="text-sm font-semibold">Logout</p>
                                                            <p class="text-xs text-gray-500">Keluar dari akun</p>
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

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 scrollbar-hide">
                <div class="px-4 sm:px-6 lg:px-8 py-8 pb-24 lg:pb-8">
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-sm">
                            <div class="flex">
                                <div class="shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-700">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                            <div class="flex">
                                <div class="shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-700">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    {{-- Bottom Navigation (Mobile) --}}
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/90 backdrop-blur-xl border-t border-gray-100 safe-bottom">
        <div class="flex items-stretch">
            @php
                $navItems = [
                    [
                        'route' => 'wakasek.dashboard',
                        'active' => request()->routeIs('wakasek.dashboard'),
                        'label' => 'Beranda',
                        'icon_outline' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>',
                        'icon_filled' => '<path d="M11.47 3.84a.75.75 0 011.06 0l8.99 9a.75.75 0 11-1.06 1.06l-1.03-1.03v7.38a1.5 1.5 0 01-1.5 1.5h-4.5a.75.75 0 01-.75-.75v-3.75a.75.75 0 00-.75-.75h-1.5a.75.75 0 00-.75.75v3.75a.75.75 0 01-.75.75h-4.5a1.5 1.5 0 01-1.5-1.5v-7.38l-1.03 1.03a.75.75 0 01-1.06-1.06l8.99-9z"></path>',
                        'guard' => null,
                    ],
                    [
                        'route' => 'wakasek.monitoring.agenda',
                        'active' => request()->routeIs('wakasek.monitoring.*'),
                        'label' => 'Agenda',
                        'icon_outline' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                        'icon_filled' => '<path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 6a.75.75 0 00-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 000-1.5h-3.75V6z" clip-rule="evenodd"></path>',
                        'guard' => null,
                    ],
                    [
                        'route' => 'wakasek.teaching.index',
                        'active' => request()->routeIs('wakasek.teaching.*'),
                        'label' => 'Kinerja',
                        'icon_outline' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>',
                        'icon_filled' => '<path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 003 3.5v17A1.5 1.5 0 004.5 22h15a1.5 1.5 0 001.5-1.5V7.621a1.5 1.5 0 00-.44-1.06l-4.12-4.122A1.5 1.5 0 0015.378 2H4.5zm2.25 8.25a.75.75 0 000 1.5H12a.75.75 0 000-1.5H6.75zm0 3a.75.75 0 000 1.5H12a.75.75 0 000-1.5H6.75zm0 3a.75.75 0 000 1.5H12a.75.75 0 000-1.5H6.75z" clip-rule="evenodd"></path>',
                        'guard' => null,
                    ],
                    [
                        'route' => 'wakasek.evaluation.index',
                        'active' => request()->routeIs('wakasek.evaluation.*'),
                        'label' => 'Evaluasi',
                        'icon_outline' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>',
                        'icon_filled' => '<path d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v15.75c0 1.035.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875V4.125c0-1.036-.84-1.875-1.875-1.875h-.75zM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 01-1.875-1.875V8.625zM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 013 19.875v-6.75z"></path>',
                        'guard' => null,
                    ],
                    [
                        'route' => 'wakasek.daily-attendance.index',
                        'active' => request()->routeIs('wakasek.daily-attendance.*'),
                        'label' => 'Kehadiran',
                        'icon_outline' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                        'icon_filled' => '<path fill-rule="evenodd" d="M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5zM12.75 6a.75.75 0 00-1.5 0v6c0 .199.079.39.22.53l3 3a.75.75 0 101.06-1.06l-2.78-2.78V6z" clip-rule="evenodd"></path>',
                        'guard' => 'attendance',
                    ],
                ];
            @endphp

            @foreach($navItems as $item)
                @if($item['guard'])
                    @feature($item['guard'])
                    <a href="{{ route($item['route']) }}"
                       class="flex-1 flex flex-col items-center justify-center py-2 relative {{ $item['active'] ? 'text-blue-600' : 'text-gray-400' }}">
                        @if($item['active'])
                            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-blue-600 rounded-full"></div>
                        @endif
                        <div class="relative">
                            @if($item['active'])
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    {!! $item['icon_filled'] !!}
                                </svg>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {!! $item['icon_outline'] !!}
                                </svg>
                            @endif
                        </div>
                        <span class="text-[10px] mt-0.5 {{ $item['active'] ? 'font-bold' : 'font-medium' }}">{{ $item['label'] }}</span>
                    </a>
                    @endfeature
                @else
                <a href="{{ route($item['route']) }}"
                   class="flex-1 flex flex-col items-center justify-center py-2 relative {{ $item['active'] ? 'text-blue-600' : 'text-gray-400' }}">
                    @if($item['active'])
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-8 h-0.5 bg-blue-600 rounded-full"></div>
                    @endif
                    <div class="relative">
                        @if($item['active'])
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                {!! $item['icon_filled'] !!}
                            </svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $item['icon_outline'] !!}
                            </svg>
                        @endif
                    </div>
                    <span class="text-[10px] mt-0.5 {{ $item['active'] ? 'font-bold' : 'font-medium' }}">{{ $item['label'] }}</span>
                </a>
                @endif
            @endforeach
        </div>
    </nav>

    @stack('scripts')
    </div>
</body>
</html>
