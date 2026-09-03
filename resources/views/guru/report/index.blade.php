{{-- resources/views/guru/report/index.blade.php --}}
@extends('layouts.guru')

@section('title', 'Laporan Guru')
@section('header', 'Laporan Guru')

@section('content')
<div class="space-y-4 sm:space-y-6 pb-24 sm:pb-6">

    {{-- Hero Header --}}
    <div class="bg-linear-to-br from-blue-600 to-indigo-600 rounded-2xl p-5 sm:p-7 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 left-1/4 w-32 h-32 bg-white/5 rounded-full blur-xl"></div>
        <div class="relative">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">Laporan Guru</h2>
                    <p class="text-[11px] text-blue-100">Rekap presensi dan agenda mengajar</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards (horizontal scroll on mobile) --}}
    <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-4 gap-3 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0">
            <div class="snap-center shrink-0 w-35 sm:w-auto bg-linear-to-br from-indigo-500 to-indigo-600 rounded-2xl p-4 sm:p-5 flex flex-col relative overflow-hidden shadow-lg shadow-indigo-200/40">
                <div class="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -mt-6 -mr-6"></div>
                <div class="relative">
                    <div class="w-8 h-8 bg-white/20 backdrop-blur rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <p class="text-[9px] font-bold text-indigo-100 uppercase tracking-wider">Total Kelas</p>
                    <p class="text-2xl sm:text-3xl font-black text-white -mt-0.5">{{ $stats['total_classes'] }}</p>
                </div>
            </div>
            <div class="snap-center shrink-0 w-35 sm:w-auto bg-linear-to-br from-emerald-500 to-emerald-600 rounded-2xl p-4 sm:p-5 flex flex-col relative overflow-hidden shadow-lg shadow-emerald-200/40">
                <div class="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -mt-6 -mr-6"></div>
                <div class="relative">
                    <div class="w-8 h-8 bg-white/20 backdrop-blur rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path></svg>
                    </div>
                    <p class="text-[9px] font-bold text-emerald-100 uppercase tracking-wider">Total Siswa</p>
                    <p class="text-2xl sm:text-3xl font-black text-white -mt-0.5">{{ number_format($stats['total_students']) }}</p>
                </div>
            </div>
            <div class="snap-center shrink-0 w-35 sm:w-auto bg-linear-to-br from-blue-500 to-blue-600 rounded-2xl p-4 sm:p-5 flex flex-col relative overflow-hidden shadow-lg shadow-blue-200/40">
                <div class="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -mt-6 -mr-6"></div>
                <div class="relative">
                    <div class="w-8 h-8 bg-white/20 backdrop-blur rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-[9px] font-bold text-blue-100 uppercase tracking-wider">Total Presensi</p>
                    <p class="text-2xl sm:text-3xl font-black text-white -mt-0.5">{{ number_format($stats['total_attendance']) }}</p>
                </div>
            </div>
            <div class="snap-center shrink-0 w-35 sm:w-auto bg-linear-to-br from-cyan-500 to-teal-600 rounded-2xl p-4 sm:p-5 flex flex-col relative overflow-hidden shadow-lg shadow-cyan-200/40">
                <div class="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-full -mt-6 -mr-6"></div>
                <div class="relative">
                    <div class="w-8 h-8 bg-white/20 backdrop-blur rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5S19.832 5.477 21 6.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <p class="text-[9px] font-bold text-cyan-100 uppercase tracking-wider">Total Agenda</p>
                    <p class="text-2xl sm:text-3xl font-black text-white -mt-0.5">{{ number_format($stats['total_agendas']) }}</p>
                </div>
            </div>
        </div>
        <p class="sm:hidden mt-1 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">Geser</p>
    </div>

    {{-- Class List + Export --}}
    <div class="space-y-3 sm:space-y-0 sm:grid sm:grid-cols-5 sm:gap-6">
        {{-- Class List with tap-to-expand --}}
        <div class="sm:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900">Rekap per Kelas</h3>
                <span class="text-[10px] font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded-lg">{{ $classes->count() }} kelas</span>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($classes as $class)
                <div x-data="{ expanded: false }"
                     class="transition-colors hover:bg-gray-50/50">
                    {{-- Tap row to expand --}}
                    <button type="button" @click="expanded = !expanded"
                            class="w-full flex items-center justify-between gap-3 px-4 sm:px-6 py-3.5 sm:py-4 text-left active:bg-gray-50 transition-colors focus:outline-none">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-9 h-9 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($class->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-900 truncate">{{ $class->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('guru.report.export', ['class_id' => $class->id, 'month' => date('m'), 'year' => date('Y')]) }}"
                               @click.stop
                               class="px-3 py-2 bg-emerald-50 text-emerald-700 rounded-xl text-[10px] font-bold uppercase tracking-wider hover:bg-emerald-100 active:scale-95 transition-all shadow-sm flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                <span class="hidden sm:inline">Excel</span>
                            </a>
                            <svg class="w-4 h-4 text-gray-300 transition-transform duration-200 shrink-0" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>

                    {{-- Expanded panel: inline export form --}}
                    <div x-show="expanded" x-collapse x-cloak class="border-t border-gray-50 bg-gray-50/30 px-4 sm:px-6 py-4">
                        <form action="{{ route('guru.report.export') }}" method="GET" class="space-y-3">
                            <input type="hidden" name="class_id" value="{{ $class->id }}">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <select name="month" id="month_{{ $class->id }}" required
                                            class="block w-full px-3 py-2 bg-white border-0 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all text-[11px] font-semibold text-gray-700"
                                            placeholder="Bulan">
                                        <option value="">Bulan</option>
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ date('m') == $m ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('M') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <select name="year" id="year_{{ $class->id }}" required
                                            class="block w-full px-3 py-2 bg-white border-0 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all text-[11px] font-semibold text-gray-700"
                                            placeholder="Tahun">
                                        <option value="">Tahun</option>
                                        @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                            <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <button type="submit"
                                    class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[11px] font-bold uppercase tracking-wider transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Download Excel
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="px-6 py-12 text-center">
                    <p class="text-[10px] font-bold text-gray-300 uppercase tracking-widest">Belum ada kelas</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Export Form Panel --}}
        <div class="sm:col-span-2">
            {{-- Mobile: collapsible --}}
            <div class="sm:hidden" x-data="{ open: false }">
                <button @click="open = !open"
                        class="w-full bg-white rounded-2xl border border-gray-100 px-4 py-3.5 flex items-center justify-between gap-3 active:bg-gray-50 transition-colors text-left focus:outline-none">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">Export Kustom</p>
                            <p class="text-[10px] text-gray-500">Pilih kelas & bulan</p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-collapse x-cloak>
                    <form action="{{ route('guru.report.export') }}" method="GET" class="bg-white rounded-2xl border border-gray-100 border-t-0 rounded-t-none p-4 space-y-4">
                        @include('guru.report._export_form', ['prefix' => 'mobile_'])
                    </form>
                </div>
            </div>

            {{-- Desktop: always visible --}}
            <div class="hidden sm:block bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900">Export Kustom</h3>
                </div>
                <form action="{{ route('guru.report.export') }}" method="GET" class="p-6 space-y-4">
                    @include('guru.report._export_form')
                </form>
            </div>
        </div>
    </div>

    {{-- Agenda Export --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-visible" x-data="{ agendaExportOpen: false }">
        {{-- Header - clickable on mobile to toggle --}}
        <button @click="agendaExportOpen = !agendaExportOpen"
                class="lg:hidden w-full px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors"
                     :class="agendaExportOpen ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-500'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <span class="text-xs sm:text-sm font-bold text-gray-800">Rekap Agenda Mengajar</span>
                    <p class="text-[10px] text-gray-400 font-medium">{{ number_format($stats['total_agendas']) }} agenda tersedia</p>
                </div>
            </div>
            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                 :class="{ 'rotate-180': agendaExportOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        {{-- Desktop header (always visible) --}}
        <div class="hidden lg:flex px-4 sm:px-6 py-4 border-b border-gray-100 flex-col sm:flex-row sm:items-center sm:justify-between gap-1 rounded-t-2xl">
            <div>
                <h3 class="text-sm font-bold text-gray-900">Rekap Agenda Mengajar</h3>
                <p class="text-[11px] text-gray-500">Unduh agenda guru berdasarkan tanggal, kelas, mapel, dan status</p>
            </div>
            <span class="text-[10px] font-bold text-teal-700 bg-teal-50 px-2.5 py-1 rounded-lg self-start sm:self-auto">{{ number_format($stats['total_agendas']) }} agenda</span>
        </div>

        {{-- Form content - collapsible on mobile, always visible on desktop --}}
        <div x-show="agendaExportOpen" x-collapse x-cloak class="lg:!block">
            <form method="GET" class="p-4 sm:p-6 space-y-4 border-t border-gray-50 lg:border-t-0">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3 items-end">
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Mulai</label>
                        <input type="date" name="start_date" value="{{ now()->startOfMonth()->toDateString() }}" required
                               class="block w-full h-[42px] px-3 py-2 bg-gray-50 border-0 rounded-xl focus:ring-2 focus:ring-teal-500 transition-all text-sm text-gray-800">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sampai</label>
                        <input type="date" name="end_date" value="{{ now()->toDateString() }}" required
                               class="block w-full h-[42px] px-3 py-2 bg-gray-50 border-0 rounded-xl focus:ring-2 focus:ring-teal-500 transition-all text-sm text-gray-800">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kelas</label>
                        <select name="class_id" id="agenda_export_class_id"
                                class="block w-full h-[42px] px-3 py-2 bg-gray-50 border-0 rounded-xl focus:ring-2 focus:ring-teal-500 transition-all text-sm text-gray-800">
                            <option value="">Semua kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Mapel</label>
                        <select name="subject_id" id="agenda_export_subject_id"
                                class="block w-full h-[42px] px-3 py-2 bg-gray-50 border-0 rounded-xl focus:ring-2 focus:ring-teal-500 transition-all text-sm text-gray-800">
                            <option value="">Semua mapel</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</label>
                        <select name="status" id="agenda_export_status"
                                class="block w-full h-[42px] px-3 py-2 bg-gray-50 border-0 rounded-xl focus:ring-2 focus:ring-teal-500 transition-all text-sm text-gray-800">
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="all">Semua status</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <button type="submit" formaction="{{ route('guru.report.agenda.export.excel') }}"
                            class="w-full h-[42px] py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs uppercase tracking-widest shadow-sm transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Excel
                    </button>
                    <button type="submit" formaction="{{ route('guru.report.agenda.export.pdf') }}"
                            class="w-full h-[42px] py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold text-xs uppercase tracking-widest shadow-sm transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        Download PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof TomSelect !== 'undefined') {
        function initTS(id, placeholder, onChange) {
            var el = document.getElementById(id);
            if (el) {
                var opts = { placeholder: placeholder, allowEmptyOption: true, dropdownParent: 'body' };
                if (onChange) opts.onChange = onChange;
                new TomSelect(el, opts);
            }
        }
        initTS('export_class_id', 'Pilih Kelas...');
        initTS('export_period', 'Pilih Periode...', function(value) {
            if (value) {
                var opt = this.getOption(value);
                if (opt) {
                    document.getElementById('hidden_month').value = opt.dataset.month;
                    document.getElementById('hidden_year').value = opt.dataset.year;
                }
            }
        });
        initTS('mobile_export_class_id', 'Pilih Kelas...');
        initTS('mobile_export_period', 'Pilih Periode...', function(value) {
            if (value) {
                var opt = this.getOption(value);
                if (opt) {
                    document.getElementById('mobile_hidden_month').value = opt.dataset.month;
                    document.getElementById('mobile_hidden_year').value = opt.dataset.year;
                }
            }
        });
        @foreach($classes as $class)
        initTS('month_{{ $class->id }}', 'Bulan');
        initTS('year_{{ $class->id }}', 'Tahun');
        @endforeach
    }
});
</script>
@endpush
@endsection
