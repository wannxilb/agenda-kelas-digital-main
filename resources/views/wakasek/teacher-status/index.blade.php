{{-- resources/views/wakasek/teacher-status/index.blade.php --}}
@extends('layouts.wakasek')

@section('title', 'Izin / Sakit / Tugas Luar Guru')
@section('header', 'Izin / Sakit / Tugas Luar Guru')

@push('styles')
<style>
    .chip-scroll::-webkit-scrollbar { display: none; }
    .chip-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .anim-card { animation: fadeSlideUp 0.4s ease-out both; }
    [x-cloak] { display: none !important; }
    .tap-active:active { transform: scale(0.97); }
</style>
@endpush

@section('content')
@php
    $badge = [
        'pending' => ['bg-amber-50 text-amber-700 border-amber-200', 'bg-amber-500', 'Menunggu'],
        'approved' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-500', 'Disetujui'],
        'rejected' => ['bg-rose-50 text-rose-700 border-rose-200', 'bg-rose-500', 'Ditolak'],
        'cancelled' => ['bg-gray-100 text-gray-500 border-gray-200', 'bg-gray-400', 'Dibatalkan'],
    ];
    $hasFilter = request()->anyFilled(['type', 'date']);
    $filterTypeLabel = match (request('type')) {
        'izin' => 'Izin',
        'sakit' => 'Sakit',
        'tugas_luar' => 'Tugas Luar',
        default => 'Semua tipe',
    };
    $typeBadge = [
        'izin' => ['bg-amber-50', 'text-amber-600'],
        'sakit' => ['bg-orange-50', 'text-orange-600'],
        'tugas_luar' => ['bg-blue-50', 'text-blue-600'],
    ];
    $filterDateLabel = request('date') ? \Carbon\Carbon::parse(request('date'))->translatedFormat('d M Y') : 'Semua tanggal';
@endphp

<div class="space-y-4 sm:space-y-6 pb-28 lg:pb-8"
     x-data="{
        filterOpen: false,
        activeFilters: {{ collect(['type', 'date'])->filter(fn($k) => request($k))->count() }},
        actionModal: null,
        actionId: null,
        rejectionReason: '',
        cancellationReason: '',
        substituteId: ''
     }"
     x-init="filterOpen = window.matchMedia('(min-width: 1024px)').matches">
    {{-- Hero Header --}}
    <div class="bg-linear-to-br from-blue-600 to-indigo-700 rounded-2xl p-5 sm:p-7 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 left-1/4 w-32 h-32 bg-white/5 rounded-full blur-xl"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">Persetujuan Izin / Sakit / Tugas Luar</h2>
                    <p class="text-[11px] text-blue-100">Tinjau dan proses pengajuan guru</p>
                </div>
            </div>
            <a href="{{ route('wakasek.teacher-status.report') }}"
               class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2.5 bg-white/15 backdrop-blur border border-white/20 text-white rounded-xl text-xs font-bold hover:bg-white/25 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Rekap Bulanan
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide -mx-1 px-1">
        @foreach(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'all' => 'Semua'] as $value => $label)
            <a href="{{ route('wakasek.teacher-status.index', array_merge(request()->except('tab'), ['tab' => $value])) }}"
               class="shrink-0 px-4 py-2.5 rounded-xl text-xs font-bold border transition-all duration-200
                      {{ $tab === $value ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20 border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                {{ $label }}
                <span class="ml-1.5 opacity-70">{{ $value === 'pending' ? $pendingCount : ($value === 'approved' ? $approvedCount : $allCount) }}</span>
            </a>
        @endforeach
    </div>

    {{-- Filter (collapsible) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Toggle bar --}}
        <button type="button" @click="filterOpen = !filterOpen"
                class="w-full px-4 py-3 sm:px-6 sm:py-4 flex items-center justify-between lg:pointer-events-none">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors"
                     :class="filterOpen ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-500'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <div class="flex items-center gap-2">
                        <span class="text-xs sm:text-sm font-bold text-gray-800">Filter Pencarian</span>
                        <span x-show="activeFilters > 0" x-cloak class="w-5 h-5 rounded-full bg-blue-600 text-white text-[9px] font-black flex items-center justify-center" x-text="activeFilters"></span>
                    </div>
                    @if($hasFilter)
                        <p class="text-[10px] text-blue-600 font-semibold">{{ $filterTypeLabel }} · {{ $filterDateLabel }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($hasFilter)
                    <a href="{{ route('wakasek.teacher-status.index', ['tab' => $tab]) }}"
                       class="px-2 py-1 bg-red-50 text-red-500 rounded-lg text-[10px] font-bold"
                       onclick="event.stopPropagation()">Reset</a>
                @endif
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 lg:hidden" :class="{ 'rotate-180': filterOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </button>

        {{-- Expandable form --}}
        <div x-show="filterOpen" x-transition x-cloak class="lg:!block">
            <form method="GET" action="{{ route('wakasek.teacher-status.index') }}"
                  class="px-4 pb-4 sm:px-6 sm:pb-5 border-t border-gray-50 pt-4">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Tipe Pengajuan</label>
                        <div class="relative">
                            <select name="type" class="appearance-none w-full px-3 py-2.5 pr-9 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold">
                                <option value="" {{ request('type') ? '' : 'selected' }}>Semua tipe</option>
                                <option value="izin" {{ request('type') === 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ request('type') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="tugas_luar" {{ request('type') === 'tugas_luar' ? 'selected' : '' }}>Tugas Luar</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Tanggal</label>
                        <input type="date" name="date" value="{{ request('date') }}"
                               class="w-full px-3 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold">
                    </div>
                </div>
                <div class="flex justify-end pt-3">
                    <button type="submit"
                            class="w-full sm:w-auto px-6 sm:px-10 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-blue-700 transition-all flex items-center justify-center gap-2 tap-active">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="space-y-3">
        @forelse($statuses as $s)
        @php
            $badgeClass = $badge[$s->status] ?? $badge['pending'];
            $typeClass = $typeBadge[$s->type] ?? $typeBadge['izin'];
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden anim-card">
            <div class="p-4 sm:p-5">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $typeClass[0] }}">
                        @if($s->type === 'izin')
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        @elseif($s->type === 'sakit')
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m8-3a8 8 0 11-16 0 8 8 0 0116 0z"></path>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $s->teacher?->name ?? '-' }}</p>
                            <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badgeClass[0] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $badgeClass[1] }}"></span> {{ $badgeClass[2] }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $s->typeLabel() }} ·
                            {{ \Carbon\Carbon::parse($s->date)->translatedFormat('d F Y') }}
                            @if($s->date_end && $s->date_end->toDateString() !== $s->date->toDateString())
                                – {{ \Carbon\Carbon::parse($s->date_end)->translatedFormat('d F Y') }}
                            @endif
                            @if($s->start_time && $s->end_time)
                                · {{ substr($s->start_time, 0, 5) }}–{{ substr($s->end_time, 0, 5) }}
                            @endif
                        </p>
                        @if($s->note)
                            <p class="text-sm text-gray-600 mt-2 leading-relaxed">{{ $s->note }}</p>
                        @endif
                        <div class="flex flex-wrap items-center gap-2 mt-2.5">
                            @if($s->attachment)
                                <a href="{{ route('wakasek.teacher-status.attachment', $s) }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-600 hover:underline">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7.586a2 2 0 00-2.828 0l-5.657 5.657a4 4 0 005.657 5.657l5.656-5.657a1 1 0 00-1.414-1.414l-5.657 5.657a2 2 0 01-2.828-2.829l5.657-5.657a4 4 0 015.657 5.657l-5.657 5.657"></path>
                                    </svg>
                                    Lampiran
                                </a>
                            @endif
                            @if($s->substituteTeacher)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-lg text-[10px] font-bold">
                                    Pengganti: {{ $s->substituteTeacher->name }}
                                </span>
                            @endif
                            @if($s->isRejected() && $s->rejection_reason)
                                <span class="text-[11px] text-rose-600 font-medium">Alasan: {{ $s->rejection_reason }}</span>
                            @endif
                            @if($s->isCancelled() && $s->cancellation_reason)
                                <span class="text-[11px] text-gray-500 font-medium">Alasan: {{ $s->cancellation_reason }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                @if($s->isPending())
                <div class="flex flex-wrap items-center gap-2 mt-4 pt-4 border-t border-gray-100">
                    <button type="button" @click="actionModal='approve'; actionId={{ $s->id }}; substituteId=''"
                            class="flex-1 sm:flex-none px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 active:scale-95 transition-all flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        Setujui
                    </button>
                    <button type="button" @click="actionModal='reject'; actionId={{ $s->id }}; rejectionReason=''"
                            class="flex-1 sm:flex-none px-4 py-2.5 bg-white border border-rose-200 text-rose-600 rounded-xl text-xs font-bold hover:bg-rose-50 active:scale-95 transition-all flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Tolak
                    </button>
                    <a href="{{ route('wakasek.teacher-status.show', $s) }}"
                       class="px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-600 rounded-xl text-xs font-bold hover:bg-gray-100 transition-all">Detail</a>
                </div>
                @else
                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-end">
                    <a href="{{ route('wakasek.teacher-status.show', $s) }}"
                       class="px-4 py-2 bg-gray-50 border border-gray-200 text-gray-600 rounded-xl text-xs font-bold hover:bg-gray-100 transition-all">Detail</a>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-800">Tidak Ada Pengajuan</h3>
            <p class="text-xs text-gray-400 mt-1">Tidak ada pengajuan pada tab ini.</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($statuses->hasPages())
    <div class="flex justify-center pt-2">
        {{ $statuses->links() }}
    </div>
    @endif

    {{-- Approve Modal --}}
    <div x-show="actionModal === 'approve'" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="actionModal = null"></div>
        <div class="relative bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-md overflow-hidden p-5 sm:p-6">
            <h3 class="text-base font-bold text-gray-900">Setujui Pengajuan</h3>
            <p class="text-xs text-gray-500 mt-1">Konfirmasi persetujuan pengajuan ini. Anda dapat menunjuk guru pengganti (opsional).</p>
            <form :action="`{{ route('wakasek.teacher-status.approve', ':id') }}`.replace(':id', actionId)" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-700">Guru Pengganti <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <select name="substitute_teacher_id" x-model="substituteId"
                            class="w-full px-3 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 transition-all text-sm text-gray-700">
                        <option value="">Tidak ada pengganti</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="actionModal = null"
                            class="h-11 flex-1 bg-gray-100 text-gray-600 rounded-xl text-xs font-bold hover:bg-gray-200 transition-all">Batal</button>
                    <button type="submit" class="h-11 flex-1 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all">Setujui</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div x-show="actionModal === 'reject'" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="actionModal = null"></div>
        <div class="relative bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-md overflow-hidden p-5 sm:p-6">
            <h3 class="text-base font-bold text-gray-900">Tolak Pengajuan</h3>
            <p class="text-xs text-gray-500 mt-1">Alasan penolakan wajib diisi dan akan ditampilkan ke guru.</p>
            <form :action="`{{ route('wakasek.teacher-status.reject', ':id') }}`.replace(':id', actionId)" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-700">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea name="rejection_reason" x-model="rejectionReason" rows="3" required
                              placeholder="Tuliskan alasan penolakan..."
                              class="w-full px-3 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-rose-500 transition-all text-sm resize-none"></textarea>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="actionModal = null"
                            class="h-11 flex-1 bg-gray-100 text-gray-600 rounded-xl text-xs font-bold hover:bg-gray-200 transition-all">Batal</button>
                    <button type="submit" class="h-11 flex-1 bg-rose-600 text-white rounded-xl text-xs font-bold hover:bg-rose-700 transition-all">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
