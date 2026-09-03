{{-- resources/views/guru/agenda/create.blade.php --}}
@extends('layouts.guru')

@section('title', 'Buat Jurnal')
@section('header', 'Jurnal Baru')

@section('content')
<div class="space-y-4 sm:space-y-6 pb-28 sm:pb-6"
     x-data="{
         todayCount: {{ $todaySchedules->pluck('class_id')->unique()->count() }},
         autoClassId: {{ $autoClassId ? "'$autoClassId'" : 'null' }},
         todayClasses: @js($todaySchedules->pluck('class_id')->unique()->values()),
         filled: 0,
          total: 7,
         get pct() { return Math.round((this.filled / this.total) * 100) },
         init() { window.__agendaForm = this; }
     }">

    {{-- Slim progress bar (mobile) --}}
    <div class="sm:hidden fixed top-0 left-0 right-0 z-30 h-1 bg-gray-100">
        <div class="h-full bg-linear-to-r from-indigo-500 to-blue-500 transition-all duration-300 ease-out"
             :style="`width: ${pct}%`"></div>
    </div>

    <nav class="flex items-center gap-1.5 text-xs sm:text-sm text-gray-500">
        <a href="{{ route('guru.agenda.index') }}" class="hover:text-indigo-600 transition-colors font-medium">Jurnal</a>
        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        <span class="text-gray-900 font-semibold">Jurnal Baru</span>
    </nav>

    {{-- Today's Schedule Hint --}}
    <div x-show="todayCount > 0" x-transition x-cloak
         class="relative overflow-hidden bg-linear-to-r from-indigo-50 to-blue-50 border border-indigo-100 rounded-2xl p-4">
        <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-200/20 rounded-full -mr-8 -mt-8 blur-2xl"></div>
        <div class="relative flex items-start gap-3">
            <div class="w-9 h-9 bg-indigo-100 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-indigo-900">Jadwal Hari Ini</p>
                <p class="text-xs text-indigo-600 mt-0.5">Anda memiliki <span class="font-black" x-text="todayCount"></span> kelas hari ini. Kelas sudah dipilih otomatis.</p>
            </div>
            <div class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse shrink-0 mt-2"></div>
        </div>
    </div>



    {{-- Mobile section jump chips --}}
    <div class="sm:hidden flex gap-2 overflow-x-auto no-scrollbar -mx-1 px-1">
        <a href="#section-jadwal"
           class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-gray-200 rounded-full text-xs font-semibold text-gray-600 active:scale-95 transition-transform shadow-sm">
            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Jadwal Kelas
        </a>
        <a href="#section-konten"
           class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-gray-200 rounded-full text-xs font-semibold text-gray-600 active:scale-95 transition-transform shadow-sm">
            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Isi Jurnal
        </a>
    </div>

    <form id="guruAgendaForm" action="{{ route('guru.agenda.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($selected_schedule)
        <input type="hidden" name="schedule_id" value="{{ $selected_schedule->id }}">
        @endif
        @include('partials.agenda-location-fields')

        <div class="space-y-4 sm:space-y-6">

            {{-- ===== SECTION: JADWAL KELAS ===== --}}
            <div id="section-jadwal" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-8 scroll-mt-20">
                <div class="flex items-center gap-3 mb-5 sm:mb-6">
                    <div class="w-10 h-10 bg-linear-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-sm shadow-indigo-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-base sm:text-lg font-bold text-gray-900">Buat Jurnal Baru</h2>
                        <p class="text-xs sm:text-sm text-gray-500">Dokumentasikan materi dan kegiatan belajar mengajar</p>
                    </div>
                </div>

                @if($errors->any())
                <div class="mb-5 bg-rose-50 border border-rose-200 rounded-xl p-4 space-y-1">
                    <p class="text-xs font-bold text-rose-700 uppercase tracking-wider">Perbaiki kesalahan berikut:</p>
                    <ul class="list-disc list-inside text-xs text-rose-600">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Quick-pick chips for today's classes (mobile-only one-tap select) --}}
                <div x-show="todayCount > 0" x-cloak class="mb-4 sm:hidden">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Pilih Cepat &mdash; Kelas Hari Ini</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($todaySchedules->pluck('class_id')->unique() as $tcid)
                            @php $tClass = $classes->firstWhere('id', $tcid); @endphp
                            @if($tClass)
                            <button type="button"
                                    onclick="window.__selectGuruClass && window.__selectGuruClass('{{ $tClass->id }}')"
                                    class="quick-class-chip inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-bold border transition-all active:scale-95 bg-indigo-50 border-indigo-200 text-indigo-700"
                                    data-class-id="{{ $tClass->id }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                {{ $tClass->name }}
                            </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div class="field-group">
                        <label for="date" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-gray-700">
                            Tanggal <span class="text-red-500">*</span>
                            <svg class="field-check w-3.5 h-3.5 text-emerald-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required
                                   class="js-track w-full pl-9 pr-3 py-3 sm:py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all text-sm @error('date') ring-2 ring-red-500 @enderror">
                        </div>
                        @error('date') <p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="field-group">
                        <label for="class_id" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-gray-700">
                            Kelas <span class="text-red-500">*</span>
                            <svg class="field-check w-3.5 h-3.5 text-emerald-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </label>
                        <select name="class_id" id="class_id" required class="js-track w-full text-sm @error('class_id') border-red-500 @enderror">
                            <option value="">Pilih Kelas</option>
                            @foreach($classes as $class)
                                @php
                                    $isToday = $todaySchedules->where('class_id', $class->id)->isNotEmpty();
                                    $isSelected = old('class_id') == $class->id
                                        || ($selected_schedule && $selected_schedule->class_id == $class->id)
                                        || ($autoClassId && $autoClassId == $class->id);
                                @endphp
                                @if($isToday)
                                <option value="{{ $class->id }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                                @endif
                            @endforeach
                        </select>
                        @error('class_id') <p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="field-group">
                        <label for="subject_id" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-gray-700">
                            Mata Pelajaran <span class="text-red-500">*</span>
                            <svg class="field-check w-3.5 h-3.5 text-emerald-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </label>
                        <select name="subject_id" id="subject_id" required class="js-track w-full text-sm @error('subject_id') border-red-500 @enderror">
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ (old('subject_id') == $subject->id || ($selected_schedule && $selected_schedule->subject_id == $subject->id)) ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id') <p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="field-group">
                        <label for="room" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-gray-700">
                            Ruangan <span class="text-red-500">*</span>
                            <svg class="field-check w-3.5 h-3.5 text-emerald-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </label>
                        <select name="room" id="room" required class="js-track w-full text-sm @error('room') border-red-500 @enderror">
                            <option value="">Pilih Ruangan</option>
                            @foreach($defaultRooms as $r)
                                <option value="{{ $r }}" {{ old('room') == $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                        @error('room') <p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p> @enderror
                        <p id="room-hint" class="text-[11px] text-indigo-500 mt-1 hidden flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Ruangan menyesuaikan jadwal Anda
                        </p>
                        <p id="room-fallback-hint" class="text-[11px] text-amber-500 mt-1 hidden flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Tidak ada jadwal hari ini — menampilkan semua ruangan
                        </p>
                    </div>
                </div>
            </div>

            {{-- ===== SECTION: KONTEN JURNAL ===== --}}
            <div id="section-konten" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-8 scroll-mt-20">
                <div class="flex items-center gap-3 mb-5 sm:mb-6">
                    <div class="w-10 h-10 bg-linear-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center text-white shadow-sm shadow-emerald-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-base sm:text-lg font-bold text-gray-900">Isi Jurnal</h2>
                        <p class="text-xs sm:text-sm text-gray-500">Ceritakan apa yang terjadi di kelas</p>
                    </div>
                </div>

                <div class="space-y-5 sm:space-y-6">
                    <div class="field-group">
                        <label for="title" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-gray-700">
                            Judul / Materi Pokok <span class="text-red-500">*</span>
                            <svg class="field-check w-3.5 h-3.5 text-emerald-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               placeholder="Contoh: Pengenalan Dasar OOP PHP"
                               class="js-track w-full px-4 py-3 sm:py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all text-sm @error('title') ring-2 ring-red-500 @enderror"
                               maxlength="255">
                        <div class="flex items-center justify-between mt-1">
                            @error('title') <p class="text-[11px] text-red-500 font-medium">{{ $message }}</p> @enderror
                            <p class="text-[11px] text-gray-400 ml-auto"><span id="titleCount">0</span>/255</p>
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="description" class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-gray-700">
                            Detail Aktivitas <span class="text-red-500">*</span>
                            <svg class="field-check w-3.5 h-3.5 text-emerald-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </label>
                        <textarea name="description" id="description" rows="5" required
                                  placeholder="Jelaskan apa saja yang dipelajari, progress materi, atau catatan khusus..."
                                  class="js-track w-full px-4 py-3 sm:py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all text-sm resize-y min-h-[140px] sm:min-h-[120px] @error('description') ring-2 ring-red-500 @enderror">{{ old('description') }}</textarea>
                        <div class="flex items-center justify-between mt-1">
                            @error('description') <p class="text-[11px] text-red-500 font-medium">{{ $message }}</p> @enderror
                            <p class="text-[11px] text-gray-400 ml-auto"><span id="descCount">0</span> karakter</p>
                        </div>
                    </div>

                    {{-- Lampiran Dokumen --}}
                    <div class="field-group">
                        <label class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-gray-700">
                            Lampiran Dokumen
                            <span class="text-xs font-normal text-gray-400">(opsional)</span>
                            <svg class="field-check w-3.5 h-3.5 text-emerald-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </label>
                        <div class="relative group">
                            <input type="file" name="attachment" id="attachment" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept=".pdf,.doc,.docx,.jpg,.png,.xlsx">
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 sm:p-8 text-center group-hover:border-indigo-400 group-hover:bg-indigo-50/50 transition-all duration-300">
                                <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 group-hover:bg-white transition-all shadow-sm">
                                    <svg class="w-6 h-6 text-gray-400 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                </div>
                                <p class="text-sm font-bold text-gray-900" id="fileNameText">Klik atau Seret File ke Sini</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-2">PDF, DOC, JPG, PNG, XLSX (Max 2MB)</p>
                            </div>
                        </div>
                        @error('attachment') <p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Status --}}
                    <div class="field-group">
                        <label class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                            Status Jurnal
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="flex p-1 bg-gray-100 rounded-xl w-full sm:w-auto">
                            <label class="flex-1 sm:flex-none text-center cursor-pointer relative group">
                                <input type="radio" name="status" value="published" {{ old('status', 'published') == 'published' ? 'checked' : '' }} class="peer sr-only">
                                <div class="py-2.5 px-5 text-xs font-bold rounded-lg text-gray-500 peer-checked:bg-white peer-checked:text-indigo-600 peer-checked:shadow-sm transition-all group-active:scale-95 flex items-center gap-1.5 justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    Terbitkan
                                </div>
                            </label>
                            <label class="flex-1 sm:flex-none text-center cursor-pointer relative group">
                                <input type="radio" name="status" value="draft" {{ old('status') == 'draft' ? 'checked' : '' }} class="peer sr-only">
                                <div class="py-2.5 px-5 text-xs font-bold rounded-lg text-gray-500 peer-checked:bg-white peer-checked:text-gray-900 peer-checked:shadow-sm transition-all group-active:scale-95 flex items-center gap-1.5 justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    Draft
                                </div>
                            </label>
                        </div>
                        @error('status') <p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Helper tips (mobile only) --}}
                    <div class="sm:hidden bg-amber-50 border border-amber-100 rounded-xl p-3 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                        <p class="text-[11px] text-amber-700 leading-relaxed">Tulis detail aktivitas sejelas mungkin &mdash; jurnal ini akan menjadi rekam jejak pembelajaran yang bisa dilihat oleh wakasek kurikulum.</p>
                    </div>
                </div>

                {{-- Desktop footer --}}
                <div class="hidden sm:flex items-center justify-between gap-4 mt-8 pt-6 border-t border-gray-100">
                    <p class="text-xs text-gray-400 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Jurnal dapat disimpan sebagai draft atau langsung diterbitkan
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('guru.agenda.index') }}"
                           class="px-5 py-2.5 text-sm font-semibold text-gray-400 hover:text-rose-600 transition-all rounded-xl hover:bg-rose-50 active:scale-95">
                            Batal
                        </a>
                        <button type="submit" id="submitBtn"
                                class="inline-flex items-center justify-center px-6 py-2.5 bg-linear-to-r from-indigo-600 to-blue-600 text-white rounded-xl text-sm font-bold hover:from-indigo-700 hover:to-blue-700 transition-all shadow-lg shadow-indigo-200 disabled:opacity-60 disabled:cursor-not-allowed active:scale-95">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Simpan Jurnal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile: Sticky Bottom Bar with progress --}}
        <div class="sm:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-xl border-t border-gray-100 safe-bottom shadow-2xl">
            <div class="px-4 pt-2.5 flex items-center justify-between">
                <p class="text-[11px] font-semibold text-gray-400"><span x-text="filled"></span>/<span x-text="total"></span> bagian diisi</p>
                <p class="text-[11px] font-bold text-indigo-600" x-text="pct + '%'"></p>
            </div>
            <div class="px-4 pt-1">
                <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-linear-to-r from-indigo-500 to-blue-500 transition-all duration-300 ease-out"
                         :style="`width: ${pct}%`"></div>
                </div>
            </div>
            <div class="px-4 py-3 flex items-center gap-3">
                <a href="{{ route('guru.agenda.index') }}"
                   class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl text-xs font-bold hover:bg-gray-200 active:scale-95 transition-all text-center">
                    Batal
                </a>
                <button type="submit" id="submitBtnMobile"
                        class="flex-[2] px-4 py-3 bg-linear-to-r from-indigo-600 to-blue-600 text-white rounded-xl text-xs font-bold hover:from-indigo-700 hover:to-blue-700 active:scale-95 transition-all shadow-lg shadow-indigo-200 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan Jurnal
                </button>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .quick-class-chip.is-active {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #fff;
    }
    .quick-class-chip.is-active .w-1\2e5 { background: #fff; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const GURU_API_URL = '{{ route("guru.agenda.get-schedule-info") }}';

        const classTom = new TomSelect('#class_id', {
            create: false,
            sortField: { field: 'text', direction: 'asc' },
            placeholder: 'Pilih Kelas',
            maxOptions: null,
            onChange: function() {
                fetchGuruScheduleInfo();
                syncQuickClassChips();
                updateProgress();
            }
        });

        const subjectTom = new TomSelect('#subject_id', {
            create: false,
            sortField: { field: 'text', direction: 'asc' },
            placeholder: 'Cari Mata Pelajaran...',
            maxOptions: null,
        });

        const roomTom = new TomSelect('#room', {
            create: true,
            createFilter: function(input) { return input.length > 0; },
            placeholder: 'Pilih Ruangan',
            maxOptions: null,
            onChange: function() { updateProgress(); }
        });

        window.__selectGuruClass = function(classId) {
            if (classTom) classTom.setValue(classId);
        };

        function syncQuickClassChips() {
            var current = classTom ? classTom.getValue() : '';
            document.querySelectorAll('.quick-class-chip').forEach(function(chip) {
                if (current && chip.dataset.classId === current) {
                    chip.classList.add('is-active');
                } else {
                    chip.classList.remove('is-active');
                }
            });
        }

        function buildGuruRoomOptions(rooms) {
            if (!roomTom) return;
            var prevVal = roomTom.getValue();
            roomTom.clearOptions();
            roomTom.addOption({ value: '', text: 'Pilih Ruangan' });
            rooms.forEach(function(r) {
                roomTom.addOption({ value: r, text: r });
            });
            if (prevVal && rooms.indexOf(prevVal) > -1) {
                roomTom.setValue(prevVal, true);
            } else {
                roomTom.setValue('', true);
            }
            roomTom.refreshOptions(false);
        }

        function buildGuruSubjectOptions(subjects) {
            if (!subjectTom) return;
            var prevVal = subjectTom.getValue();
            subjectTom.clearOptions();
            subjectTom.addOption({ value: '', text: 'Cari Mata Pelajaran...' });
            subjects.forEach(function(s) {
                subjectTom.addOption({ value: s.id, text: s.name });
            });
            if (prevVal && subjects.some(function(s) { return String(s.id) === String(prevVal); })) {
                subjectTom.setValue(prevVal, true);
            } else {
                subjectTom.setValue('', true);
            }
            subjectTom.refreshOptions(false);
        }

        function fetchGuruScheduleInfo() {
            var classId = document.getElementById('class_id').value;
            var date = document.getElementById('date').value;
            if (!classId || !date) return;

            if (subjectTom) {
                subjectTom.clearOptions();
                subjectTom.addOption({ value: '', text: 'Memuat...' });
                subjectTom.setValue('');
                subjectTom.refreshOptions(false);
            }

            fetch(GURU_API_URL + '?class_id=' + classId + '&date=' + date)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    buildGuruSubjectOptions(data.subjects);
                    buildGuruRoomOptions(data.all_rooms);

                    var hint = document.getElementById('room-hint');
                    var fallback = document.getElementById('room-fallback-hint');
                    if (hint && fallback) {
                        if (data.has_schedule) {
                            hint.classList.remove('hidden');
                            fallback.classList.add('hidden');
                        } else {
                            hint.classList.add('hidden');
                            fallback.classList.remove('hidden');
                        }
                    }
                    updateProgress();
                }).catch(function() {
                    if (subjectTom) {
                        subjectTom.clear();
                        subjectTom.clearOptions();
                        subjectTom.addOption({ value: '', text: 'Gagal memuat' });
                        subjectTom.refreshOptions(false);
                    }
                });
        }

        var dateInput = document.getElementById('date');
        if (dateInput) {
            dateInput.addEventListener('change', function() {
                fetchGuruScheduleInfo();
                updateProgress();
            });
        }

        if (document.getElementById('class_id').value) {
            fetchGuruScheduleInfo();
        } else if (dateInput && dateInput.value) {
            fetchGuruScheduleInfo();
        }
        syncQuickClassChips();

        var titleInput = document.getElementById('title');
        var descInput = document.getElementById('description');
        var titleCount = document.getElementById('titleCount');
        var descCount = document.getElementById('descCount');

        if (titleInput && titleCount) {
            titleInput.addEventListener('input', function() {
                titleCount.textContent = titleInput.value.length;
                updateProgress();
            });
            titleCount.textContent = titleInput.value.length;
        }

        if (descInput && descCount) {
            descInput.addEventListener('input', function() {
                descCount.textContent = descInput.value.length;
                updateProgress();
            });
            descCount.textContent = descInput.value.length;
        }

        // ----- File attachment name -----
        var attachmentInput = document.getElementById('attachment');
        var fileNameText = document.getElementById('fileNameText');
        if (attachmentInput && fileNameText) {
            attachmentInput.addEventListener('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    fileNameText.textContent = file.name;
                    fileNameText.classList.add('text-indigo-600');
                } else {
                    fileNameText.textContent = 'Klik atau Seret File ke Sini';
                    fileNameText.classList.remove('text-indigo-600');
                }
            });
        }

        // ----- Progress tracking -----
        var trackedIds = ['date', 'class_id', 'subject_id', 'room', 'title', 'description', 'status'];

        function fieldFilled(id) {
            var el = document.getElementById(id);
            if (!el) return false;
            return !!(el.value && String(el.value).trim().length > 0);
        }

        function updateProgress() {
            var count = trackedIds.filter(fieldFilled).length;
            if (window.__agendaForm) {
                window.__agendaForm.filled = count;
            }
        }

        updateProgress();

        function disableButtons() {
            var btns = ['submitBtn', 'submitBtnMobile'].map(function(id) { return document.getElementById(id); });
            btns.forEach(function(btn) {
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = 'Menyimpan...';
                }
            });
        }

        var form = document.getElementById('guruAgendaForm');
        if (form) {
            form.addEventListener('submit', disableButtons);
        }
    });

    function resetForm() {
        if (confirm('Reset semua field yang telah diisi?')) {
            var form = document.getElementById('guruAgendaForm');
            if (form) form.reset();
            var dateInput = document.getElementById('date');
            if (dateInput) dateInput.value = '{{ date("Y-m-d") }}';
            var titleCount = document.getElementById('titleCount');
            if (titleCount) titleCount.textContent = '0';
            var descCount = document.getElementById('descCount');
            if (descCount) descCount.textContent = '0';
            window.location.reload();
        }
    }
</script>
@endpush
@endsection
