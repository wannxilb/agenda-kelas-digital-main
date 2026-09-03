@extends('layouts.sekretaris')

@section('title', 'Arsip Agenda Kelas')
@section('header', 'Arsip Agenda Kelas')

@section('content')
<div class="space-y-4 sm:space-y-6 pb-8">
    {{-- Header --}}
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-48 h-48 bg-blue-50 rounded-full blur-3xl opacity-50"></div>
        <div class="relative">
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Arsip Agenda</h1>
            <p class="mt-2 text-xs sm:text-sm text-gray-500 font-medium">Jelajahi catatan kegiatan kelas sebelumnya.</p>
        </div>
    </div>

    {{-- Filter --}}
    @php $hasFilter = request()->anyFilled(['search', 'start_date', 'end_date']); @endphp
    <div x-data="{ showFilter: {{ $hasFilter ? 'true' : 'false' }} }" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <button @click="showFilter = !showFilter"
                class="w-full px-4 py-3 sm:px-6 sm:py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors"
                     :class="showFilter ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-500'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <span class="text-xs sm:text-sm font-bold text-gray-800">Filter</span>
                    @if($hasFilter)
                        <p class="text-[10px] text-blue-600 font-semibold">
                            @if(request('search')) "{{ request('search') }}" @endif
                            @if(request('start_date') || request('end_date'))
                                • {{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->translatedFormat('d M') : 'Semua' }}
                                –
                                {{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->translatedFormat('d M Y') : 'Sekarang' }}
                            @endif
                        </p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($hasFilter)
                    <a href="{{ route('sekretaris.agenda.archive') }}"
                       class="px-2 py-1 bg-red-50 text-red-500 rounded-lg text-[10px] font-bold"
                       onclick="event.stopPropagation()">Reset</a>
                @endif
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                     :class="{ 'rotate-180': showFilter }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </button>

        <div x-show="showFilter" x-collapse x-cloak>
            <form method="GET" action="{{ route('sekretaris.agenda.archive') }}"
                  class="px-4 pb-4 sm:px-6 sm:pb-5 border-t border-gray-50 pt-4 space-y-3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul agenda..."
                           class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block">Dari</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                               class="w-full px-3 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 block">Sampai</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                               class="w-full px-3 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold">
                    </div>
                </div>
                <button type="submit" class="w-full py-2.5 bg-gray-900 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-black transition-all">
                    Terapkan
                </button>
            </form>
        </div>
    </div>

    {{-- Agenda List --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 py-3.5 sm:px-6 sm:py-4 border-b border-gray-50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800">Daftar Arsip</h3>
            <span class="px-2.5 py-1 bg-gray-100 text-gray-500 rounded-lg text-[10px] font-bold">{{ $agendas->total() }} agenda</span>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-50">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tanggal</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kelas</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Mata Pelajaran</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Judul</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Guru</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($agendas as $agenda)
                    @php
                        $colors = [
                            'blue'    => ['badge' => 'bg-blue-50 text-blue-700', 'dot' => 'bg-blue-500'],
                            'indigo'  => ['badge' => 'bg-indigo-50 text-indigo-700', 'dot' => 'bg-indigo-500'],
                            'violet'  => ['badge' => 'bg-violet-50 text-violet-700', 'dot' => 'bg-violet-500'],
                            'emerald' => ['badge' => 'bg-emerald-50 text-emerald-700', 'dot' => 'bg-emerald-500'],
                            'amber'   => ['badge' => 'bg-amber-50 text-amber-700', 'dot' => 'bg-amber-500'],
                            'rose'    => ['badge' => 'bg-rose-50 text-rose-700', 'dot' => 'bg-rose-500'],
                        ];
                        $colorKeys = array_keys($colors);
                        $subjectId = $agenda->subject_id ?? 0;
                        $c = $colors[$colorKeys[$subjectId % count($colorKeys)]];
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors group cursor-pointer" onclick="previewAgenda({{ $agenda->id }})">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-9 h-9 bg-linear-to-br from-indigo-500 to-blue-600 text-white rounded-xl flex flex-col items-center justify-center shadow-sm flex-shrink-0">
                                    <span class="text-[7px] font-black uppercase leading-none opacity-60">{{ $agenda->date->translatedFormat('M') }}</span>
                                    <span class="text-xs font-black leading-none">{{ $agenda->date->format('d') }}</span>
                                </div>
                                <span class="text-xs font-bold text-gray-700">{{ $agenda->date->translatedFormat('d M Y') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-50 text-blue-700 rounded-md">{{ $agenda->class->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 {{ $c['badge'] }} rounded-md text-[10px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full {{ $c['dot'] }}"></span>
                                {{ $agenda->subject->name ?? 'Umum' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $agenda->title }}</td>
                        <td class="px-6 py-4 text-xs text-gray-500 font-medium">{{ $agenda->teacher->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                </div>
                                <p class="text-sm font-bold text-gray-400">Belum ada arsip</p>
                                <p class="text-[10px] text-gray-300 mt-0.5">Arsip agenda akan muncul di sini</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="lg:hidden divide-y divide-gray-50">
            @forelse($agendas as $agenda)
            @php
                $colors = [
                    'blue'    => ['badge' => 'bg-blue-50 text-blue-700', 'dot' => 'bg-blue-500'],
                    'indigo'  => ['badge' => 'bg-indigo-50 text-indigo-700', 'dot' => 'bg-indigo-500'],
                    'violet'  => ['badge' => 'bg-violet-50 text-violet-700', 'dot' => 'bg-violet-500'],
                    'emerald' => ['badge' => 'bg-emerald-50 text-emerald-700', 'dot' => 'bg-emerald-500'],
                    'amber'   => ['badge' => 'bg-amber-50 text-amber-700', 'dot' => 'bg-amber-500'],
                    'rose'    => ['badge' => 'bg-rose-50 text-rose-700', 'dot' => 'bg-rose-500'],
                ];
                $colorKeys = array_keys($colors);
                $subjectId = $agenda->subject_id ?? 0;
                $c = $colors[$colorKeys[$subjectId % count($colorKeys)]];
            @endphp
            <div class="p-4 sm:p-5 hover:bg-gray-50/50 transition-colors cursor-pointer active:scale-[0.98]" onclick="previewAgenda({{ $agenda->id }})">
                <div class="flex items-start gap-3">
                    {{-- Date Avatar --}}
                    <div class="w-11 h-11 bg-linear-to-br from-indigo-500 to-blue-600 text-white rounded-xl flex flex-col items-center justify-center shadow-lg shadow-indigo-200 flex-shrink-0">
                        <span class="text-[7px] font-black uppercase leading-none opacity-60 mb-0.5">{{ $agenda->date->translatedFormat('M') }}</span>
                        <span class="text-sm font-black leading-none">{{ $agenda->date->format('d') }}</span>
                    </div>

                    {{-- Content --}}
                    <div class="min-w-0 flex-1">
                        <h4 class="text-sm font-bold text-gray-900 leading-snug line-clamp-2">{{ $agenda->title }}</h4>

                        <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 {{ $c['badge'] }} rounded-md text-[9px] font-bold">
                                <span class="w-1 h-1 rounded-full {{ $c['dot'] }}"></span>
                                {{ $agenda->subject->name ?? 'Umum' }}
                            </span>
                            <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded-md text-[9px] font-bold">{{ $agenda->class->name }}</span>
                        </div>

                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-flex items-center gap-1 text-[10px] text-gray-400 font-medium">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                {{ $agenda->teacher->name ?? '-' }}
                            </span>
                        </div>
                    </div>

                    {{-- Tap indicator --}}
                    <div class="flex-shrink-0 self-center">
                        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-10 text-center">
                <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                </div>
                <p class="text-sm font-bold text-gray-400">Belum ada arsip</p>
                <p class="text-[10px] text-gray-300 mt-0.5">Arsip agenda akan muncul di sini</p>
            </div>
            @endforelse
        </div>

        @if($agendas->hasPages())
        <div class="px-4 py-3 border-t border-gray-50 bg-gray-50/30 sm:px-6">
            {{ $agendas->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Preview Modal --}}
<div id="previewModal" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm hidden items-end sm:items-center justify-center z-50">
    <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-slide-up sm:animate-fade-in">
        <div class="h-5 sm:hidden bg-white flex items-center justify-center pt-2">
            <div class="w-10 h-1 bg-gray-200 rounded-full"></div>
        </div>
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 pr-4" id="previewTitle">Detail Agenda</h3>
            <button onclick="closePreview()" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 text-gray-400 rounded-full flex items-center justify-center transition-colors flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4" id="previewBody">
            <div class="flex items-center gap-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider" id="previewLoading">
                <div class="w-4 h-4 border-2 border-gray-300 border-t-transparent rounded-full animate-spin"></div>
                Memuat data...
            </div>
        </div>
        <div class="h-6 sm:hidden bg-white"></div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @keyframes slide-up { from { transform: translateY(100%); } to { transform: translateY(0); } }
    @keyframes fade-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    .animate-slide-up { animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
    .animate-fade-in { animation: fade-in 0.2s ease-out; }
</style>
@endpush

@push('scripts')
<script>
    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value || '';
        return div.innerHTML;
    }

    function previewAgenda(id) {
        const modal = document.getElementById('previewModal');
        const title = document.getElementById('previewTitle');
        const body = document.getElementById('previewBody');

        title.textContent = 'Detail Agenda';
        body.innerHTML = `
            <div class="flex items-center gap-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider" id="previewLoading">
                <div class="w-4 h-4 border-2 border-gray-300 border-t-transparent rounded-full animate-spin"></div>
                Memuat data...
            </div>
        `;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        fetch(`{{ url('sekretaris/agenda') }}/${id}/preview`)
            .then(r => {
                if (!r.ok) {
                    throw new Error('Preview request failed');
                }

                return r.json();
            })
            .then(data => {
                title.textContent = data.title || 'Detail Agenda';

                let html = '';

                html += `<div class="flex flex-wrap gap-1.5">`;
                if (data.subject_name) {
                    html += `<span class="inline-flex items-center gap-1 px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-md text-[10px] font-bold"><span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>${data.subject_name}</span>`;
                }
                if (data.class_name) {
                    html += `<span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-md text-[10px] font-bold">${data.class_name}</span>`;
                }
                if (data.room) {
                    html += `<span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-md text-[10px] font-bold">${data.room}</span>`;
                }
                html += `</div>`;

                html += `<div class="flex items-center gap-3 text-[11px] text-gray-500 font-medium">`;
                html += `<span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>${data.date}</span>`;
                html += `<span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>${data.teacher_name}</span>`;
                html += `</div>`;

                if (data.description) {
                    html += `<div class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">${escapeHtml(data.description)}</div>`;
                }

                if (data.attachments) {
                    html += `<a href="${data.attachments}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-50 text-blue-600 rounded-xl text-xs font-bold hover:bg-blue-100 transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>Lampiran</a>`;
                }

                body.innerHTML = html;
            })
            .catch(() => {
                body.innerHTML = '<p class="text-sm text-red-500 font-bold text-center py-4">Gagal memuat data.</p>';
            });
    }

    function closePreview() {
        const modal = document.getElementById('previewModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    document.getElementById('previewModal').addEventListener('click', function(e) {
        if (e.target === this) closePreview();
    });
</script>
@endpush
