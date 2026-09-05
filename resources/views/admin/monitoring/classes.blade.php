{{-- resources/views/admin/monitoring/classes.blade.php --}}
@extends('layouts.admin')

@section('title', 'Monitoring Kelas')
@section('header', 'Monitoring Kelas')

@section('content')
<div class="space-y-8 pb-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Monitoring Kelas</h1>
            <p class="text-gray-500 mt-1 font-medium">Pantau statistik kehadiran dan agenda setiap kelas secara real-time.</p>
        </div>
        <div class="flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-100 text-xs font-bold uppercase tracking-wider">
            <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></span>
            Live Monitoring
        </div>
    </div>

    <!-- Classes Grid -->
    <div id="monitoring-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($classes as $class)
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
            <!-- Card Header -->
            <div class="bg-linear-to-br from-blue-600 to-indigo-700 p-5 text-white relative">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20"><path d="M10.394 2.827a1 1 0 00-.788 0l-7 3a1 1 0 000 1.848l7 3a1 1 0 00.788 0l7-3a1 1 0 000-1.848l-7-3zM14 11.595l-3.223 1.381A3.001 3.001 0 0110 15V7.103l4-1.714v6.206z"></path></svg>
                </div>
                <div class="relative">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-100">Kapasitas {{ $class['capacity'] }} Siswa</span>
                    <h3 class="text-xl font-black mt-1">{{ $class['name'] }}</h3>
                    <p class="text-blue-100/80 text-xs font-bold uppercase tracking-wider mt-1">Level {{ $class['grade_level'] }}</p>
                </div>
            </div>

            <!-- Card Body -->
            <div class="p-6 space-y-5">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-2xl">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Wali Kelas</span>
                    </div>
                    <span class="text-sm font-black text-gray-900">{{ $class['homeroom'] ?? '-' }}</span>
                </div>

                <!-- Student Progress -->
                <div>
                    <div class="flex justify-between items-end mb-2">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kepadatan Siswa</span>
                        <span class="text-xs font-black text-gray-900">{{ $class['students_count'] }} / {{ $class['capacity'] }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                        @php $studentPct = ($class['students_count'] / max($class['capacity'], 1)) * 100; @endphp
                        <div class="bg-blue-600 h-full rounded-full transition-all duration-700" style="width: {{ $studentPct }}%"></div>
                    </div>
                </div>

                <!-- Agenda Stats -->
                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div class="p-3 bg-indigo-50/50 rounded-2xl border border-indigo-100/50">
                        <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">Total Agenda</p>
                        <p class="text-lg font-black text-indigo-700">{{ $class['agendas_count'] }}</p>
                    </div>
                    <div class="p-3 bg-emerald-50/50 rounded-2xl border border-emerald-100/50 text-right">
                        <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest mb-1">Presensi</p>
                        <p class="text-lg font-black text-emerald-700">{{ $class['attendance_rate'] }}%</p>
                    </div>
                </div>

                <!-- Attendance Progress -->
                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-emerald-500 h-full rounded-full transition-all duration-700 shadow-[0_0_8px_rgba(16,185,129,0.5)]" style="width: {{ $class['attendance_rate'] }}%"></div>
                </div>

                <!-- Card Footer -->
                <div class="mt-4 pt-5 border-t border-gray-50 flex items-center justify-between">
                    <a href="{{ route('admin.classes.show', $class['id']) }}" class="inline-flex items-center text-xs font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest group/link transition-all">
                        Detail Kelas
                        <svg class="w-4 h-4 ml-1 group-hover/link:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </a>
                    <div class="flex -space-x-2">
                        @foreach(range(1, 3) as $i)
                        <div class="w-6 h-6 rounded-full border-2 border-white bg-gray-200 flex items-center justify-center text-[8px] font-bold text-gray-500 overflow-hidden">
                            <img src="https://ui-avatars.com/api/?name=User+{{ $i }}&background=random" alt="User">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script type="application/json" id="monitoring-initial-data">{!! $classesJson !!}</script>
<script>
    const MONITORING_API_URL = '{{ route('admin.monitoring.classes.api') }}';
    const CLASS_DETAIL_PREFIX = '{{ route('admin.classes.show', '__ID__') }}'.replace('/__ID__', '');

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function classCard(c) {
        const cap = Math.max(c.capacity || 1, 1);
        const studentPct = Math.min((c.students_count / cap) * 100, 100);
        const attRate = Math.min(c.attendance_rate ?? 0, 100);
        return `
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
            <div class="bg-linear-to-br from-blue-600 to-indigo-700 p-5 text-white relative">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20"><path d="M10.394 2.827a1 1 0 00-.788 0l-7 3a1 1 0 000 1.848l7 3a1 1 0 00.788 0l7-3a1 1 0 000-1.848l-7-3zM14 11.595l-3.223 1.381A3.001 3.001 0 0110 15V7.103l4-1.714v6.206z"></path></svg>
                </div>
                <div class="relative">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-100">Kapasitas ${esc(c.capacity)} Siswa</span>
                    <h3 class="text-xl font-black mt-1">${esc(c.name)}</h3>
                    <p class="text-blue-100/80 text-xs font-bold uppercase tracking-wider mt-1">Level ${esc(c.grade_level)}</p>
                </div>
            </div>
            <div class="p-6 space-y-5">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-2xl">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Wali Kelas</span>
                    </div>
                    <span class="text-sm font-black text-gray-900">${esc(c.homeroom)}</span>
                </div>
                <div>
                    <div class="flex justify-between items-end mb-2">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kepadatan Siswa</span>
                        <span class="text-xs font-black text-gray-900">${esc(c.students_count)} / ${esc(c.capacity)}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-blue-600 h-full rounded-full transition-all duration-700" style="width: ${studentPct}%"></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div class="p-3 bg-indigo-50/50 rounded-2xl border border-indigo-100/50">
                        <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">Total Agenda</p>
                        <p class="text-lg font-black text-indigo-700">${esc(c.agendas_count)}</p>
                    </div>
                    <div class="p-3 bg-emerald-50/50 rounded-2xl border border-emerald-100/50 text-right">
                        <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest mb-1">Presensi</p>
                        <p class="text-lg font-black text-emerald-700">${esc(c.attendance_rate)}%</p>
                    </div>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-emerald-500 h-full rounded-full transition-all duration-700 shadow-[0_0_8px_rgba(16,185,129,0.5)]" style="width: ${attRate}%"></div>
                </div>
                <div class="mt-4 pt-5 border-t border-gray-50 flex items-center justify-between">
                    <a href="${CLASS_DETAIL_PREFIX}/${esc(c.id)}" class="inline-flex items-center text-xs font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest group/link transition-all">
                        Detail Kelas
                        <svg class="w-4 h-4 ml-1 group-hover/link:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </a>
                    <div class="flex -space-x-2">
                        ${[1,2,3].map(function(){ return '<div class="w-6 h-6 rounded-full border-2 border-white bg-gray-200 flex items-center justify-center text-[8px] font-bold text-gray-500 overflow-hidden"></div>'; }).join('')}
                    </div>
                </div>
            </div>
        </div>`;
    }

    function renderClasses(data) {
        const grid = document.getElementById('monitoring-grid');
        if (!grid) return;
        grid.innerHTML = (data && data.length ? data : []).map(classCard).join('');
    }

    async function pollMonitoring() {
        const res = await fetch(MONITORING_API_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
        renderClasses(await res.json());
    }

    // Initial render + smart polling (no overlap, backoff, pause when tab hidden).
    const initialData = JSON.parse(document.getElementById('monitoring-initial-data').textContent || '[]');
    renderClasses(initialData);
    poll(pollMonitoring, { interval: 30000, backoffMax: 120000 });
</script>
@endpush
@endsection