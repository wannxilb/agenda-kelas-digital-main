{{-- resources/views/guru/teacher-status/_form.blade.php --}}
{{--
    Form bersama untuk create & edit pengajuan izin/tugas luar.

    Variabel yang diharapkan:
    - $formAction   : string  (route action form)
    - $formMethod   : string  ('POST' | 'PUT')
    - $submitLabel  : string
    - $teacherStatus: ?\App\Models\TeacherStatus  (null saat create)
--}}
@php
    $typeValue     = old('type', $teacherStatus?->type);
    $dateValue     = old('date', $teacherStatus?->date?->format('Y-m-d') ?? date('Y-m-d'));
    $dateEndValue  = old('date_end', $teacherStatus?->date_end?->format('Y-m-d'));
    $startTimeVal  = old('start_time', $teacherStatus?->start_time ? substr($teacherStatus->start_time, 0, 5) : null);
    $endTimeVal    = old('end_time', $teacherStatus?->end_time ? substr($teacherStatus->end_time, 0, 5) : null);
    $noteValue     = old('note', $teacherStatus?->note);
@endphp

@if($errors->any())
<div class="mb-6 bg-rose-50 border border-rose-200 rounded-xl p-4 space-y-1">
    <p class="text-xs font-bold text-rose-700 uppercase tracking-wider">Perbaiki kesalahan berikut:</p>
    <ul class="list-disc list-inside text-xs text-rose-600">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

<form id="teacherStatusForm" action="{{ $formAction }}" method="POST" enctype="multipart/form-data"
      x-data="{
        type: {{ json_encode($typeValue ?? '') }},
        multiDay: {{ $dateEndValue ? 'true' : 'false' }},
        hasTime: {{ ($startTimeVal || $endTimeVal) ? 'true' : 'false' }},
        startTime: {{ json_encode($startTimeVal ?? '') }},
        endTime: {{ json_encode($endTimeVal ?? '') }}
      }">
    @csrf
    @if($formMethod === 'PUT') @method('PUT') @endif

    <div class="space-y-5 sm:space-y-6">
        {{-- Tipe --}}
        <div class="space-y-1.5">
            <label class="block text-xs sm:text-sm font-semibold text-gray-700">Tipe <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-3 gap-2.5">
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="izin" x-model="type" class="hidden">
                    <div class="relative h-24 sm:h-20 px-3 py-3 rounded-xl text-center border transition-all flex flex-col sm:flex-row items-center justify-center sm:justify-start gap-2 sm:gap-3"
                         :class="type === 'izin' ? 'bg-amber-50 border-amber-500 shadow-md shadow-amber-100' : 'bg-gray-50 border-gray-100 hover:bg-gray-100 hover:border-gray-200'">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center transition-all shrink-0"
                             :class="type === 'izin' ? 'bg-amber-500' : 'bg-gray-200'">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0 sm:text-left">
                            <p class="text-xs sm:text-sm font-bold transition-all"
                               :class="type === 'izin' ? 'text-amber-700' : 'text-gray-600'">Izin</p>
                            <p class="hidden sm:block text-[10px] mt-0.5 leading-tight transition-all"
                               :class="type === 'izin' ? 'text-amber-500' : 'text-gray-400'">Tidak masuk mengajar</p>
                        </div>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="sakit" x-model="type" class="hidden">
                    <div class="relative h-24 sm:h-20 px-3 py-3 rounded-xl text-center border transition-all flex flex-col sm:flex-row items-center justify-center sm:justify-start gap-2 sm:gap-3"
                         :class="type === 'sakit' ? 'bg-orange-50 border-orange-500 shadow-md shadow-orange-100' : 'bg-gray-50 border-gray-100 hover:bg-gray-100 hover:border-gray-200'">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center transition-all shrink-0"
                             :class="type === 'sakit' ? 'bg-orange-500' : 'bg-gray-200'">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m8-3a8 8 0 11-16 0 8 8 0 0116 0z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0 sm:text-left">
                            <p class="text-xs sm:text-sm font-bold transition-all"
                               :class="type === 'sakit' ? 'text-orange-700' : 'text-gray-600'">Sakit</p>
                            <p class="hidden sm:block text-[10px] mt-0.5 leading-tight transition-all"
                               :class="type === 'sakit' ? 'text-orange-500' : 'text-gray-400'">Kondisi kesehatan</p>
                        </div>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="tugas_luar" x-model="type" class="hidden">
                    <div class="relative h-24 sm:h-20 px-3 py-3 rounded-xl text-center border transition-all flex flex-col sm:flex-row items-center justify-center sm:justify-start gap-2 sm:gap-3"
                         :class="type === 'tugas_luar' ? 'bg-blue-50 border-blue-500 shadow-md shadow-blue-100' : 'bg-gray-50 border-gray-100 hover:bg-gray-100 hover:border-gray-200'">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center transition-all shrink-0"
                             :class="type === 'tugas_luar' ? 'bg-blue-500' : 'bg-gray-200'">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0 sm:text-left">
                            <p class="text-xs sm:text-sm font-bold transition-all"
                               :class="type === 'tugas_luar' ? 'text-blue-700' : 'text-gray-600'">Tugas Luar</p>
                            <p class="hidden sm:block text-[10px] mt-0.5 leading-tight transition-all"
                               :class="type === 'tugas_luar' ? 'text-blue-500' : 'text-gray-400'">Dinas luar sekolah</p>
                        </div>
                    </div>
                </label>
            </div>
            @error('type')<p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>

        {{-- Tanggal --}}
        <div class="space-y-1.5">
            <div class="flex items-center justify-between">
                <label class="block text-xs sm:text-sm font-semibold text-gray-700">Tanggal <span class="text-red-500">*</span></label>
                <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                    <input type="checkbox" x-model="multiDay" class="w-3.5 h-3.5 rounded border-gray-300 text-rose-500 focus:ring-rose-500">
                    <span class="text-[11px] font-semibold text-gray-500">Banyak hari</span>
                </label>
            </div>
            <div class="grid gap-2.5" :class="multiDay ? 'grid-cols-2' : 'grid-cols-1'">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <input type="date" name="date" id="date" value="{{ $dateValue }}" min="{{ date('Y-m-d') }}" required
                           class="h-12 w-full pl-9 pr-3 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-rose-500 transition-all text-sm @error('date') ring-2 ring-red-500 @enderror">
                </div>
                <div class="relative" x-show="multiDay" x-cloak x-transition>
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <input type="date" name="date_end" id="date_end" value="{{ $dateEndValue }}" min="{{ date('Y-m-d') }}"
                           class="h-12 w-full pl-9 pr-3 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-rose-500 transition-all text-sm @error('date_end') ring-2 ring-red-500 @enderror">
                </div>
            </div>
            @error('date')<p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p>@enderror
            @error('date_end')<p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>

        {{-- Jam paruh hari --}}
        <div class="space-y-1.5">
            <div class="flex items-center justify-between">
                <label class="block text-xs sm:text-sm font-semibold text-gray-700">Jam <span class="text-gray-400 font-normal">(opsional)</span></label>
                <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                    <input type="checkbox" x-model="hasTime" class="w-3.5 h-3.5 rounded border-gray-300 text-rose-500 focus:ring-rose-500">
                    <span class="text-[11px] font-semibold text-gray-500">Paruh hari</span>
                </label>
            </div>
            <div class="grid grid-cols-2 gap-2.5" x-show="hasTime" x-cloak x-transition>
                <div>
                    <input type="time" name="start_time" x-model="startTime"
                           class="h-11 w-full px-3 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-rose-500 transition-all text-sm @error('start_time') ring-2 ring-red-500 @enderror">
                    @error('start_time')<p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input type="time" name="end_time" x-model="endTime"
                           class="h-11 w-full px-3 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-rose-500 transition-all text-sm @error('end_time') ring-2 ring-red-500 @enderror">
                    @error('end_time')<p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Keterangan --}}
        <div class="space-y-1.5">
            <label class="block text-xs sm:text-sm font-semibold text-gray-700">Keterangan <span class="text-gray-400 font-normal">(opsional)</span></label>
            <textarea name="note" id="note" rows="4"
                      placeholder="Jelaskan alasan izin, sakit, atau tugas luar..."
                      class="w-full px-4 py-3 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-rose-500 transition-all text-sm resize-none min-h-[112px] @error('note') ring-2 ring-red-500 @enderror"
                      maxlength="500">{{ $noteValue }}</textarea>
            <div class="flex items-center justify-between mt-1">
                @error('note')<p class="text-[11px] text-red-500 font-medium">{{ $message }}</p>@enderror
                <p class="text-[11px] text-gray-400 ml-auto"><span id="noteCount">0</span>/500</p>
            </div>
        </div>

        {{-- Lampiran --}}
        <div class="space-y-1.5">
            <label class="block text-xs sm:text-sm font-semibold text-gray-700">
                Lampiran Surat <span class="text-red-500">*</span>
                <span class="text-gray-400 font-normal">(wajib untuk izin, sakit, dan tugas luar — PDF/JPG/PNG maks 2MB)</span>
            </label>
            @if($teacherStatus?->attachment)
                <div class="flex items-center justify-between gap-2 bg-indigo-50/60 border border-indigo-100 rounded-xl px-3.5 py-2.5 mb-2">
                    <a href="{{ route('guru.teacher-status.attachment', $teacherStatus) }}" target="_blank"
                       class="text-xs font-semibold text-indigo-700 hover:underline inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7.586a2 2 0 00-2.828 0l-5.657 5.657a4 4 0 005.657 5.657l5.656-5.657a1 1 0 00-1.414-1.414l-5.657 5.657a2 2 0 01-2.828-2.829l5.657-5.657a4 4 0 015.657 5.657l-5.657 5.657"></path>
                        </svg>
                        Lampiran saat ini
                    </a>
                    <span class="text-[10px] text-indigo-400 font-semibold">Surat sudah ada — tidak wajib unggah ulang</span>
                </div>
            @endif
            <label class="relative flex flex-col items-center justify-center w-full h-28 border-2 border-dashed rounded-xl border-gray-200 bg-gray-50 hover:border-rose-300 hover:bg-rose-50/40 transition-all cursor-pointer">
                <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png" class="sr-only" onchange="document.getElementById('attachmentName').textContent = this.files[0] ? this.files[0].name : 'Pilih file'">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7.586a2 2 0 00-2.828 0l-5.657 5.657a4 4 0 005.657 5.657l5.656-5.657a1 1 0 00-1.414-1.414l-5.657 5.657a2 2 0 01-2.828-2.829l5.657-5.657a4 4 0 015.657 5.657l-5.657 5.657"></path>
                </svg>
                <span id="attachmentName" class="text-[11px] font-semibold text-gray-500 mt-1">{{ $teacherStatus?->attachment ? 'Pilih file baru untuk mengganti (opsional)' : 'Pilih file surat izin / keterangan / dinas' }}</span>
            </label>
            @error('attachment')<p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Info card --}}
    <div class="mt-6 bg-indigo-50/60 border border-indigo-100 rounded-xl px-4 py-3 flex items-start gap-2.5">
        <svg class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-[11px] text-indigo-700 leading-relaxed">
            @if($teacherStatus)
                Pengajuan masih berstatus <b>Menunggu</b> — perubahan akan dikirim ulang ke wakasek untuk ditinjau kembali.
            @else
                Pengajuan akan dikirim ke wakasek untuk disetujui. Status pengajuan bisa dilihat di halaman riwayat,
                dan dapat ditarik selama masih <b>Menunggu</b> persetujuan.
            @endif
        </p>
    </div>

    {{-- Desktop footer --}}
    <div class="hidden sm:flex items-center justify-between gap-4 mt-8 pt-6 border-t border-gray-100">
        <a href="{{ $teacherStatus ? route('guru.teacher-status.show', $teacherStatus) : route('guru.teacher-status.index') }}"
           class="h-11 px-5 text-sm font-semibold text-gray-500 hover:text-rose-600 transition-all rounded-xl hover:bg-rose-50 active:scale-95 inline-flex items-center justify-center">
            Batal
        </a>
        <button type="submit" id="submitBtn"
                class="h-11 inline-flex items-center justify-center px-6 bg-linear-to-r from-rose-500 to-pink-600 text-white rounded-xl text-sm font-bold hover:from-rose-600 hover:to-pink-700 transition-all shadow-lg shadow-rose-200 disabled:opacity-60 disabled:cursor-not-allowed active:scale-95">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ $submitLabel }}
        </button>
    </div>

    {{-- Mobile: Bottom Bar --}}
    <div class="sm:hidden mt-6 pt-4 border-t border-gray-100 flex items-center gap-3">
        <a href="{{ $teacherStatus ? route('guru.teacher-status.show', $teacherStatus) : route('guru.teacher-status.index') }}"
           class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl text-xs font-bold hover:bg-gray-200 active:scale-95 transition-all text-center">
            Batal
        </a>
        <button type="submit" id="submitBtnMobile"
                class="flex-1 px-4 py-3 bg-linear-to-r from-rose-500 to-pink-600 text-white rounded-xl text-xs font-bold hover:from-rose-600 hover:to-pink-700 active:scale-95 transition-all shadow-lg shadow-rose-200 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ $submitLabel }}
        </button>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var noteInput = document.getElementById('note');
        var noteCount = document.getElementById('noteCount');
        if (noteInput && noteCount) {
            noteInput.addEventListener('input', function() { noteCount.textContent = noteInput.value.length; });
            noteCount.textContent = noteInput.value.length;
        }

        function disableButtons() {
            var btns = [document.getElementById('submitBtn'), document.getElementById('submitBtnMobile')];
            btns.forEach(function(btn) {
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = @js($sendingLabel ?? 'Mengirim...');
                }
            });
        }

        var form = document.getElementById('teacherStatusForm');
        if (form) {
            form.addEventListener('submit', disableButtons);
        }
    });
</script>
@endpush
