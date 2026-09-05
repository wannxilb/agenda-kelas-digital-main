{{-- resources/views/admin/monitoring/teachers.blade.php --}}
@extends('layouts.admin')

@section('title', 'Monitoring Guru')
@section('header', 'Monitoring Guru')

@section('content')
<div class="space-y-8 pb-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Monitoring Guru</h1>
            <p class="text-gray-500 mt-1 font-medium">Pantau aktivitas mengajar dan produktivitas guru secara real-time.</p>
        </div>
        <div class="flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 rounded-2xl border border-indigo-100 text-xs font-bold uppercase tracking-wider">
            <span class="w-2 h-2 bg-indigo-500 rounded-full mr-2 animate-pulse"></span>
            Live Activities
        </div>
    </div>

    <!-- Search Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" name="search" id="teacher-search" value="{{ request('search') }}" 
                       placeholder="Cari nama guru atau NIP..." 
                       class="block w-full pl-12 pr-4 py-3 bg-gray-50 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm">
            </div>
            <button type="submit" class="inline-flex items-center justify-center px-8 py-3 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
                Cari Data
            </button>
        </form>
    </div>

    <!-- Teachers Grid -->
    <div id="monitoring-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($teachers as $teacher)
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
            <!-- Card Header -->
            <div class="bg-linear-to-br from-indigo-600 to-violet-700 p-6 text-white relative">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path></svg>
                </div>
                <div class="flex items-center relative">
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-xl font-black border border-white/30 shadow-inner">
                        {{ strtoupper(substr($teacher['name'], 0, 1)) }}
                    </div>
                    <div class="ml-4 min-w-0">
                        <h3 class="text-lg font-black truncate leading-tight">{{ $teacher['name'] }}</h3>
                        <p class="text-indigo-100/80 text-[10px] font-bold uppercase tracking-widest mt-1">NIP: {{ $teacher['nip'] ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Card Body -->
            <div class="p-6 space-y-5">
                <!-- Subjects Tags -->
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Mata Pelajaran</p>
                    <div class="flex flex-wrap gap-2">
                        @forelse(array_slice($teacher['subjects'] ?? [], 0, 3) as $subject)
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-xl text-[10px] font-black border border-indigo-100 uppercase tracking-wider">
                                {{ $subject }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-400 italic">Belum ada mapel</span>
                        @endforelse
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-indigo-200 transition-colors group/stat">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1 group-hover/stat:text-indigo-500 transition-colors">Total Agenda</p>
                        <p class="text-2xl font-black text-gray-900">{{ $teacher['agendas_count'] ?? 0 }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-emerald-200 transition-colors group/stat">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1 group-hover/stat:text-emerald-500 transition-colors">Bulan Ini</p>
                        <p class="text-2xl font-black text-gray-900">{{ $teacher['monthly_agendas_count'] ?? 0 }}</p>
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="mt-4 pt-5 border-t border-gray-50">
                    <a href="{{ route('admin.teachers.show', $teacher['id']) }}" class="flex items-center justify-center w-full py-3 bg-indigo-50 text-indigo-700 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all group/btn shadow-sm">
                        Lihat Profil Guru
                        <svg class="w-4 h-4 ml-2 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script type="application/json" id="monitoring-initial-data">{!! $teachersJson !!}</script>
<script>
    const MONITORING_API_URL = '{{ route('admin.monitoring.teachers.api') }}';

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function teacherCard(t) {
        const subjectTags = (t.subjects && t.subjects.length)
            ? t.subjects.map(function (n) { return '<span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-xl text-[10px] font-black border border-indigo-100 uppercase tracking-wider">' + esc(n) + '</span>'; }).join('')
            : '<span class="text-xs text-gray-400 italic">Belum ada mapel</span>';
        return `
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
            <div class="bg-linear-to-br from-indigo-600 to-violet-700 p-6 text-white relative">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path></svg>
                </div>
                <div class="flex items-center relative">
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-xl font-black border border-white/30 shadow-inner">${esc(t.initial)}</div>
                    <div class="ml-4 min-w-0">
                        <h3 class="text-lg font-black truncate leading-tight">${esc(t.name)}</h3>
                        <p class="text-indigo-100/80 text-[10px] font-bold uppercase tracking-widest mt-1">NIP: ${esc(t.nip)}</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-5">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Mata Pelajaran</p>
                    <div class="flex flex-wrap gap-2">${subjectTags}</div>
                </div>
                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-indigo-200 transition-colors group/stat">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1 group-hover/stat:text-indigo-500 transition-colors">Total Agenda</p>
                        <p class="text-2xl font-black text-gray-900">${esc(t.agendas_count)}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-emerald-200 transition-colors group/stat">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1 group-hover/stat:text-emerald-500 transition-colors">Bulan Ini</p>
                        <p class="text-2xl font-black text-gray-900">${esc(t.monthly_agendas_count)}</p>
                    </div>
                </div>
                <div class="mt-4 pt-5 border-t border-gray-50">
                    <a href="/admin/teachers/${esc(t.id)}" class="flex items-center justify-center w-full py-3 bg-indigo-50 text-indigo-700 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all group/btn shadow-sm">
                        Lihat Profil Guru
                        <svg class="w-4 h-4 ml-2 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                </div>
            </div>
        </div>`;
    }

    function renderTeachers(data) {
        const grid = document.getElementById('monitoring-grid');
        if (!grid) return;
        grid.innerHTML = (data && data.length ? data : []).map(teacherCard).join('');
    }

    async function pollMonitoring() {
        const params = new URLSearchParams();
        const searchValue = document.getElementById('teacher-search')?.value || '';
        if (searchValue) params.set('search', searchValue);
        const res = await fetch(MONITORING_API_URL + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        renderTeachers(await res.json());
    }

    // Initial render + smart polling (no overlap, backoff, pause when tab hidden).
    const initialData = JSON.parse(document.getElementById('monitoring-initial-data').textContent || '[]');
    renderTeachers(initialData);
    poll(pollMonitoring, { interval: 30000, backoffMax: 120000 });
</script>
@endpush
@endsection