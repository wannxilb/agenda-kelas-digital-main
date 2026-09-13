{{-- resources/views/sekretaris/attendance/index.blade.php --}}
@extends('layouts.sekretaris')

@section('title', 'Presensi Siswa')
@section('header', 'Presensi Siswa')

@section('content')
@php
    $resolvedStatuses = $resolvedStatuses ?? [];
    $initialStatusMap = [];
    if (isset($students)) {
        foreach($students as $student) {
            $manualStatus = $student->attendances->first()?->status;
            $resolvedStatus = $resolvedStatuses[$student->id] ?? null;
            if ($resolvedStatus === 'not_yet') {
                $resolvedStatus = null;
            }
            $initialStatusMap[$student->id] = $manualStatus ?? $resolvedStatus ?? 'belum';
        }
    }
    $initialStats = [
        'present' => 0,
        'sick' => 0,
        'excused' => 0,
        'late' => 0,
        'absent' => 0,
        'belum' => 0
    ];
    foreach ($initialStatusMap as $status) {
        if (isset($initialStats[$status])) {
            $initialStats[$status]++;
        }
    }
@endphp
<div class="space-y-4 sm:space-y-6" x-data='attendanceManager(@json($initialStats))'>

    {{-- Header Description --}}
    <div>
        <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
            Kelola kehadiran siswa kelas <strong class="text-gray-700">{{ optional($classes->first())->name ?? 'Tidak ada kelas' }}</strong> secara harian.
        </p>
    </div>

    {{-- Date & Class Selector Card --}}
    <form method="GET" action="{{ route('sekretaris.attendance.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 sm:p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-gray-800">Pilih Tanggal</h3>
                    <p class="text-[11px] text-gray-500">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</p>
                </div>
                @if(\Carbon\Carbon::parse($date)->isToday())
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-lg border border-emerald-100 uppercase tracking-wider">Hari Ini</span>
                @endif
            </div>
            <div class="flex gap-3">
                <div class="flex-1">
                    <input type="date" name="date" id="date" value="{{ $date }}" onchange="this.form.submit()"
                           class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm font-bold text-gray-900">
                </div>
                <button type="submit"
                        class="flex-shrink-0 px-5 py-3 bg-blue-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-200/50 hover:bg-blue-700 active:scale-95 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
        </div>
    </form>

    @if($selectedClassId)
        {{-- Locked Banner --}}
        <template x-if="isLocked">
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="-translate-y-2 opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-amber-800">Data Terkunci</p>
                    <p class="text-[11px] text-amber-600">Presensi sudah dikunci dan tidak dapat diubah dari halaman ini.</p>
                </div>
            </div>
        </template>

        {{-- Stats Cards (Horizontal Scroll on Mobile) --}}
        <div class="-mx-4 sm:mx-0 px-4 sm:px-0" @update-stats.window="updateStats($event.detail)">
            <div class="flex sm:grid sm:grid-cols-6 gap-3 sm:gap-3 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0">

                <div class="snap-center shrink-0 w-[120px] sm:w-auto bg-emerald-50/50 rounded-2xl border border-emerald-100 p-3.5 sm:p-4 flex flex-col justify-center relative overflow-hidden">
                    <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Hadir</p>
                    <p class="text-xl sm:text-2xl font-black text-emerald-600" x-text="stats.present">0</p>
                </div>

                <div class="snap-center shrink-0 w-[120px] sm:w-auto bg-orange-50/50 rounded-2xl border border-orange-100 p-3.5 sm:p-4 flex flex-col justify-center">
                    <p class="text-[9px] sm:text-[10px] font-bold text-orange-600/70 uppercase tracking-wider mb-1">Sakit</p>
                    <p class="text-xl sm:text-2xl font-black text-orange-600" x-text="stats.sick">0</p>
                </div>

                <div class="snap-center shrink-0 w-[120px] sm:w-auto bg-sky-50/50 rounded-2xl border border-sky-100 p-3.5 sm:p-4 flex flex-col justify-center">
                    <p class="text-[9px] sm:text-[10px] font-bold text-sky-600/70 uppercase tracking-wider mb-1">Izin</p>
                    <p class="text-xl sm:text-2xl font-black text-sky-600" x-text="stats.excused">0</p>
                </div>

                <div class="snap-center shrink-0 w-[120px] sm:w-auto bg-amber-50/50 rounded-2xl border border-amber-100 p-3.5 sm:p-4 flex flex-col justify-center">
                    <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-1">Telat</p>
                    <p class="text-xl sm:text-2xl font-black text-amber-600" x-text="stats.late">0</p>
                </div>

                <div class="snap-center shrink-0 w-[120px] sm:w-auto bg-rose-50/50 rounded-2xl border border-rose-100 p-3.5 sm:p-4 flex flex-col justify-center">
                    <p class="text-[9px] sm:text-[10px] font-bold text-rose-600/70 uppercase tracking-wider mb-1">Alpha</p>
                    <p class="text-xl sm:text-2xl font-black text-rose-600" x-text="stats.absent">0</p>
                </div>

                <div class="snap-center shrink-0 w-[120px] sm:w-auto bg-gray-50/80 rounded-2xl border border-gray-100 p-3.5 sm:p-4 flex flex-col justify-center">
                    <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Belum absen</p>
                    <p class="text-xl sm:text-2xl font-black text-gray-500" x-text="stats.belum">0</p>
                </div>

            </div>
            <p class="sm:hidden mt-1 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">← Geser →</p>
        </div>

        {{-- Search & Actions Bar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-3 sm:p-4 flex items-center gap-2">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" x-model="search" placeholder="Cari nama siswa..."
                           class="block w-full pl-9 pr-3 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all text-sm">
                </div>
                <button type="button" x-show="!isLocked" @click="markAllPresent()"
                        class="flex-shrink-0 flex items-center gap-1.5 px-4 py-2.5 bg-emerald-50 text-emerald-700 rounded-xl text-[11px] font-bold border border-emerald-100 hover:bg-emerald-100 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="hidden sm:inline">Semua Hadir</span>
                    <span class="sm:hidden">Semua</span>
                </button>
            </div>
        </div>

        {{-- Student List --}}
        <form action="{{ route('sekretaris.attendance.store') }}" method="POST">
            @csrf
            <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="space-y-3 sm:space-y-0 sm:bg-white sm:rounded-2xl sm:shadow-sm sm:border sm:border-gray-100 sm:overflow-hidden">
                {{-- Desktop Table Header --}}
                <div class="hidden sm:grid sm:grid-cols-12 gap-4 px-6 py-4 border-b border-gray-50 bg-gray-50/50">
                    <div class="col-span-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Siswa</div>
                    <div class="col-span-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</div>
                    <div class="col-span-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Keterangan</div>
                </div>

                @foreach($students as $student)
                    @php
                        $currentAttendance = $student->attendances->first();
                        $statusLabels = ['present' => 'Hadir', 'sick' => 'Sakit', 'excused' => 'Izin', 'late' => 'Telat', 'absent' => 'Alpha', 'unknown' => 'Perlu Verifikasi'];
                        $statusColors = [
                            'present' => 'bg-emerald-50 text-emerald-700',
                            'sick' => 'bg-orange-50 text-orange-700',
                            'excused' => 'bg-sky-50 text-sky-700',
                            'late' => 'bg-amber-50 text-amber-700',
                            'absent' => 'bg-rose-50 text-rose-700',
                            'unknown' => 'bg-amber-50 text-amber-700'
                        ];
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sm:border-0 sm:shadow-none sm:rounded-none sm:border-b sm:border-gray-50 last:sm:border-b-0"
                         x-data="{
                            status: '{{ $initialStatusMap[$student->id] ?? 'belum' }}',
                            expanded: false,
                            init() {
                                this.$watch('status', (val, old) => {
                                    if (val !== old) {
                                        window.dispatchEvent(new CustomEvent('update-stats', { detail: { newStatus: val, oldStatus: old } }));
                                    }
                                });
                            }
                         }"
                         x-show='shouldShow(@json($student->name))'>

                        {{-- Mobile: Compact row - tap to expand --}}
                        <button type="button" @click="expanded = !expanded" class="sm:hidden w-full px-4 py-3 flex items-center gap-3 text-left active:bg-gray-50 transition-colors">
                            <div class="w-9 h-9 bg-linear-to-br from-blue-500 to-indigo-600 text-white rounded-xl flex items-center justify-center text-xs font-bold shadow-sm flex-shrink-0">
                                {{ strtoupper(substr($student->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900 truncate">{{ $student->name }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $student->nis }}</p>
                            </div>
                            {{-- Status Badge --}}
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold flex-shrink-0"
                                  :class="{
                                      'bg-emerald-50 text-emerald-700': status === 'present',
                                      'bg-orange-50 text-orange-700': status === 'sick',
                                      'bg-sky-50 text-sky-700': status === 'excused',
                                      'bg-amber-50 text-amber-700': status === 'late',
                                      'bg-rose-50 text-rose-700': status === 'absent',
                                      'bg-amber-50 text-amber-700': status === 'unknown',
                                      'bg-gray-100 text-gray-500': status === 'belum'
                                  }"
                                  x-text="status === 'present' ? 'Hadir' : status === 'sick' ? 'Sakit' : status === 'excused' ? 'Izin' : status === 'late' ? 'Telat' : status === 'absent' ? 'Alpha' : status === 'unknown' ? 'Perlu Verifikasi' : 'Belum absen'">
                            </span>
                            {{-- Chevron --}}
                            <svg class="w-4 h-4 text-gray-300 flex-shrink-0 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        {{-- Mobile: Expanded panel --}}
                        <div x-show="expanded" x-collapse x-cloak class="sm:hidden px-4 pb-4 space-y-3 border-t border-gray-50"
                             :class="isLocked ? 'opacity-50 pointer-events-none' : ''">
                            {{-- Status Pills (mobile) --}}
                            <div class="flex flex-wrap gap-2 pt-3">
                                <span x-show="status === 'belum'"
                                      class="inline-block px-3 py-1.5 rounded-xl text-[11px] font-bold border bg-gray-100 text-gray-500 border-gray-200">Belum absen</span>
                                <template x-for="opt in [
                                    {v: 'present', l: 'Hadir'},
                                    {v: 'sick', l: 'Sakit'},
                                    {v: 'excused', l: 'Izin'},
                                    {v: 'late', l: 'Telat'},
                                    {v: 'absent', l: 'Alpha'}
                                ]">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="attendance[{{ $student->id }}][status]" :value="opt.v" x-model="status" class="hidden">
                                        <span class="inline-block px-3 py-1.5 rounded-xl text-[11px] font-bold border transition-all"
                                              :class="{
                                                  'bg-emerald-500 text-white border-emerald-500 shadow-md shadow-emerald-200': status === opt.v && opt.v === 'present',
                                                  'bg-orange-500 text-white border-orange-500 shadow-md shadow-orange-200': status === opt.v && opt.v === 'sick',
                                                  'bg-sky-500 text-white border-sky-500 shadow-md shadow-sky-200': status === opt.v && opt.v === 'excused',
                                                  'bg-amber-500 text-white border-amber-500 shadow-md shadow-amber-200': status === opt.v && opt.v === 'late',
                                                  'bg-rose-500 text-white border-rose-500 shadow-md shadow-rose-200': status === opt.v && opt.v === 'absent',
                                                  'bg-gray-50 text-gray-600 border-gray-200': status !== opt.v
                                              }"
                                              x-text="opt.l"></span>
                                    </label>
                                </template>
                            </div>
                            {{-- Note --}}
                            <input type="text" name="attendance[{{ $student->id }}][note]" value="{{ $currentAttendance->note ?? '' }}"
                                   :readonly="isLocked"
                                   :class="isLocked ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500'"
                                   class="w-full border-0 rounded-xl px-4 py-2.5 text-xs font-medium placeholder:text-gray-300 transition-all"
                                   placeholder="Catatan (opsional)...">
                        </div>

                        {{-- Desktop Row --}}
                        <div class="hidden sm:grid sm:grid-cols-12 sm:gap-4 sm:items-center px-6 py-4" :class="isLocked ? 'opacity-50 pointer-events-none' : ''">
                            <div class="col-span-3 flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 bg-linear-to-br from-blue-500 to-indigo-600 text-white rounded-xl flex items-center justify-center text-xs font-bold shadow-sm flex-shrink-0">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ $student->name }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $student->nis }}</p>
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="flex flex-wrap gap-1.5">
                                    <span x-show="status === 'belum'"
                                          class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold border bg-gray-100 text-gray-500 border-gray-200">Belum absen</span>
                                    <template x-for="opt in [
                                        {v: 'present', l: 'Hadir'},
                                        {v: 'sick', l: 'Sakit'},
                                        {v: 'excused', l: 'Izin'},
                                        {v: 'late', l: 'Telat'},
                                        {v: 'absent', l: 'Alpha'}
                                    ]">
                                        <label class="cursor-pointer">
                                            <input type="radio" name="attendance[{{ $student->id }}][status]" :value="opt.v" x-model="status" class="hidden">
                                            <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold border transition-all"
                                                  :class="{
                                                      'bg-emerald-500 text-white border-emerald-500 shadow-md shadow-emerald-200': status === opt.v && opt.v === 'present',
                                                      'bg-orange-500 text-white border-orange-500 shadow-md shadow-orange-200': status === opt.v && opt.v === 'sick',
                                                      'bg-sky-500 text-white border-sky-500 shadow-md shadow-sky-200': status === opt.v && opt.v === 'excused',
                                                      'bg-amber-500 text-white border-amber-500 shadow-md shadow-amber-200': status === opt.v && opt.v === 'late',
                                                      'bg-rose-500 text-white border-rose-500 shadow-md shadow-rose-200': status === opt.v && opt.v === 'absent',
                                                      'bg-gray-50 text-gray-600 border-gray-200': status !== opt.v
                                                  }"
                                                  x-text="opt.l"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                            <div class="col-span-5">
                                <input type="text" name="attendance[{{ $student->id }}][note]" value="{{ $currentAttendance->note ?? '' }}"
                                       :readonly="isLocked"
                                       :class="isLocked ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500'"
                                       class="w-full border-0 rounded-xl px-3 py-2 text-xs font-medium placeholder:text-gray-300 transition-all"
                                       placeholder="Catatan (opsional)...">
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Empty Search --}}
                <div class="hidden sm:block" x-show="search && filteredCount === 0" x-cloak>
                    <div class="px-6 py-10 text-center">
                        <p class="text-sm font-bold text-gray-500">Siswa tidak ditemukan</p>
                        <p class="text-xs text-gray-400 mt-1">Coba kata kunci lain</p>
                    </div>
                </div>
            </div>

            {{-- Mobile Empty Search --}}
            <div class="sm:hidden" x-show="search && filteredCount === 0" x-cloak>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                    <p class="text-sm font-bold text-gray-500">Siswa tidak ditemukan</p>
                    <p class="text-xs text-gray-400 mt-1">Coba kata kunci lain</p>
                </div>
            </div>

            {{-- Spacer for bottom nav on mobile --}}
            <div class="h-12 sm:hidden"></div>

            {{-- Save Button --}}
            <div x-show="!isLocked" x-cloak class="sm:mt-4 mb-2 sm:mb-0">
                <button type="submit"
                        class="w-full py-3.5 sm:py-3 bg-linear-to-r from-blue-600 to-indigo-600 text-white rounded-2xl font-bold text-sm shadow-xl shadow-blue-500/20 hover:from-blue-700 hover:to-indigo-700 active:scale-[0.98] transition-all">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan Presensi
                    </span>
                </button>
            </div>
        </form>
    @else
        {{-- No Class State --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 sm:p-16 text-center">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-linear-to-br from-gray-50 to-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-gray-200">
                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-gray-800">Kelas Tidak Ditemukan</h3>
            <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-xs mx-auto">Anda belum terdaftar di kelas manapun. Hubungi admin untuk pengaturan kelas.</p>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@push('scripts')
<script>
    function attendanceManager(initialStats) {
        return {
            isLocked: {{ ($isLocked ?? false) ? 'true' : 'false' }},
            search: '',
            stats: initialStats,

            get filteredCount() {
                const items = document.querySelectorAll('[x-show*="shouldShow"]');
                let count = 0;
                items.forEach(el => {
                    if (el.style.display !== 'none') count++;
                });
                return count;
            },

            updateStats(detail) {
                if (this.stats[detail.oldStatus] !== undefined) {
                    this.stats[detail.oldStatus]--;
                }
                if (this.stats[detail.newStatus] !== undefined) {
                    this.stats[detail.newStatus]++;
                }
            },

            shouldShow(name) {
                if (!this.search) return true;
                return name.toLowerCase().includes(this.search.toLowerCase());
            },

            markAllPresent() {
                // Find all student card Alpine components and set status to present
                document.querySelectorAll('[x-data*="expanded"]').forEach(el => {
                    if (el._x_dataStack) {
                        const data = el._x_dataStack[0];
                        if (data && data.status !== undefined) {
                            data.status = 'present';
                        }
                    }
                });
            }
        }
    }
</script>
@endpush
