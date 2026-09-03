@php
    $currentClassId = old('class_id', $assignment->class_id ?? $selectedClassId ?? '');
    $currentSubjectId = old('subject_id', $assignment->subject_id ?? $selectedSubjectId ?? '');
    $currentMaxScore = old('max_score', $assignment->max_score ?? 100);
    $initialGradedCount = $students->filter(fn($s) => old("scores.{$s->id}", $gradeMap->get($s->id)?->score ?? '') !== '')->count();
    $studentNames = $students->map(fn($s) => strtolower($s->name))->values();
@endphp

<div class="space-y-4 sm:space-y-6 pb-28 sm:pb-6"
     x-data='gradeForm({
        maxScore: {{ (float) $currentMaxScore }},
        studentCount: {{ $students->count() }},
        gradedCount: {{ $initialGradedCount }},
        studentNames: @json($studentNames)
     })'>
    <nav class="flex items-center gap-1.5 text-xs sm:text-sm text-gray-500">
        <a href="{{ route('guru.grades.index') }}" class="font-medium hover:text-emerald-600">Nilai Tugas</a>
        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        <span class="font-semibold text-gray-900">{{ $assignment ? 'Edit' : 'Tambah' }}</span>
    </nav>

    <div class="sm:hidden flex gap-2 overflow-x-auto hide-scrollbar -mx-1 px-1">
        <a href="#section-tugas" class="shrink-0 inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-600 shadow-sm">
            Data Tugas
        </a>
        <a href="#section-siswa" class="shrink-0 inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-600 shadow-sm">
            Nilai Siswa
        </a>
    </div>

    <form method="POST" action="{{ $action }}" class="space-y-4 sm:space-y-6" id="gradeForm">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <section id="section-tugas" class="scroll-mt-20 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m-7 4h8m-8 4h5m-7 6h12a2 2 0 002-2V5a2 2 0 00-2-2H8.5L4 7.5V19a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-base font-bold text-gray-900">Data Tugas</h2>
                        <p class="mt-0.5 text-xs leading-relaxed text-gray-500">Tentukan identitas tugas sebelum mengisi nilai siswa.</p>
                    </div>
                </div>
            </div>

            <div class="p-5 sm:p-6">
                @if($errors->any())
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4">
                        <p class="text-xs font-black uppercase tracking-wider text-rose-700">Perbaiki data berikut:</p>
                        <ul class="mt-1.5 list-inside list-disc space-y-0.5 text-xs font-semibold text-rose-600">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-600 sm:text-[11px] sm:uppercase sm:tracking-wider sm:text-gray-500">Kelas <span class="text-rose-500">*</span></label>
                        @if($assignment)
                            <input type="hidden" name="class_id" value="{{ $currentClassId }}">
                        @endif
                        <select name="class_id" id="class_id" required {{ $assignment ? 'disabled' : '' }}
                                class="w-full rounded-xl border-0 bg-gray-50 px-4 py-3 text-sm text-gray-800 transition-all focus:bg-white focus:ring-2 focus:ring-emerald-500 sm:py-2.5 {{ $assignment ? 'cursor-not-allowed opacity-60' : '' }}">
                            <option value="">Pilih Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ (string) $currentClassId === (string) $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @if(!$assignment)
                            <p class="text-[11px] font-medium text-gray-400">Ganti kelas akan memuat ulang daftar siswa.</p>
                        @endif
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-600 sm:text-[11px] sm:uppercase sm:tracking-wider sm:text-gray-500">Mata Pelajaran <span class="text-rose-500">*</span></label>
                        @if($assignment)
                            <input type="hidden" name="subject_id" value="{{ $currentSubjectId }}">
                        @endif
                        <select name="subject_id" id="subject_id" required {{ $assignment ? 'disabled' : '' }}
                                class="w-full rounded-xl border-0 bg-gray-50 px-4 py-3 text-sm text-gray-800 transition-all focus:bg-white focus:ring-2 focus:ring-emerald-500 sm:py-2.5 {{ $assignment ? 'cursor-not-allowed opacity-60' : '' }}">
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ (string) $currentSubjectId === (string) $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 sm:text-[11px] sm:uppercase sm:tracking-wider sm:text-gray-500">Nama Tugas <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $assignment->title ?? '') }}" required maxlength="255"
                               placeholder="Contoh: Latihan Bab 3 - Relasi Database"
                               class="js-grade-track w-full rounded-xl border-0 bg-gray-50 px-4 py-3 text-sm text-gray-800 placeholder:text-gray-400 transition-all focus:bg-white focus:ring-2 focus:ring-emerald-500 sm:py-2.5">
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 sm:text-[11px] sm:uppercase sm:tracking-wider sm:text-gray-500">Keterangan Tugas <span class="text-rose-500">*</span></label>
                        <textarea name="description" rows="3" required maxlength="1000"
                                  placeholder="Jelaskan tugas yang dinilai agar mudah dilacak."
                                  class="js-grade-track min-h-28 w-full resize-y rounded-xl border-0 bg-gray-50 px-4 py-3 text-sm leading-6 text-gray-800 placeholder:text-gray-400 transition-all focus:bg-white focus:ring-2 focus:ring-emerald-500 sm:py-2.5">{{ old('description', $assignment->description ?? '') }}</textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-600 sm:text-[11px] sm:uppercase sm:tracking-wider sm:text-gray-500">Tanggal Tugas <span class="text-rose-500">*</span></label>
                        <input type="date" name="assigned_date" value="{{ old('assigned_date', $assignment?->assigned_date?->format('Y-m-d') ?? date('Y-m-d')) }}" required
                               class="js-grade-track w-full rounded-xl border-0 bg-gray-50 px-4 py-3 text-sm text-gray-800 transition-all focus:bg-white focus:ring-2 focus:ring-emerald-500 sm:py-2.5">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-600 sm:text-[11px] sm:uppercase sm:tracking-wider sm:text-gray-500">Batas Pengumpulan</label>
                        <input type="date" name="due_date" value="{{ old('due_date', $assignment?->due_date?->format('Y-m-d') ?? '') }}"
                               class="w-full rounded-xl border-0 bg-gray-50 px-4 py-3 text-sm text-gray-800 transition-all focus:bg-white focus:ring-2 focus:ring-emerald-500 sm:py-2.5">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-600 sm:text-[11px] sm:uppercase sm:tracking-wider sm:text-gray-500">Nilai Maksimal <span class="text-rose-500">*</span></label>
                        <input type="number" name="max_score" id="max_score" min="1" max="999.99" step="0.01" value="{{ $currentMaxScore }}" required
                               class="js-grade-track w-full rounded-xl border-0 bg-gray-50 px-4 py-3 text-sm text-gray-800 transition-all focus:bg-white focus:ring-2 focus:ring-emerald-500 sm:py-2.5">
                    </div>
                </div>
            </div>
        </section>

        <section id="section-siswa" class="scroll-mt-20 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 p-4 sm:p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20H7m10 0v-2a5 5 0 00-10 0v2m10-13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900">Nilai Siswa</h2>
                            <p class="text-xs text-gray-500">Isi nilai manual, atau gunakan shortcut angka di setiap siswa.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-gray-600">{{ $students->count() }} siswa</span>
                        @if($currentClassId && $students->isNotEmpty())
                            <span class="rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700">
                                <span x-text="gradedCount">0</span>/<span x-text="studentCount">{{ $students->count() }}</span> dinilai
                            </span>
                        @endif
                    </div>
                </div>

                @if($currentClassId && $students->isNotEmpty())
                    <div class="mt-4 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-300"
                             :style="'width: ' + (studentCount > 0 ? (gradedCount / studentCount * 100) : 0) + '%'"></div>
                    </div>

                    <div class="mt-4">
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input type="text" x-model="studentSearch" placeholder="Cari nama siswa..."
                                   class="w-full rounded-xl border-0 bg-gray-50 py-3 pl-9 pr-9 text-sm transition-all focus:bg-white focus:ring-2 focus:ring-emerald-500 lg:py-2.5">
                            <button type="button" x-show="studentSearch" x-cloak @click="studentSearch = ''"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            @if(!$currentClassId)
                <div class="p-8 text-center sm:p-12">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50">
                        <svg class="h-7 w-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-800">Pilih kelas terlebih dahulu</p>
                    <p class="mt-1 text-xs text-gray-400">Daftar siswa akan dimuat otomatis.</p>
                </div>
            @elseif($students->isEmpty())
                <div class="p-8 text-center sm:p-12">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50">
                        <svg class="h-7 w-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20H7m10 0v-2a5 5 0 00-10 0v2m10-13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-800">Belum ada siswa di kelas ini</p>
                    <p class="mt-1 text-xs text-gray-400">Pastikan siswa sudah masuk kelas pada tahun ajaran aktif.</p>
                </div>
            @else
                <div class="hidden border-b border-gray-100 bg-gray-50 px-5 py-3 sm:grid sm:grid-cols-12 sm:gap-4">
                    <div class="col-span-7 text-[11px] font-bold uppercase tracking-wider text-gray-500">Siswa</div>
                    <div class="col-span-3 text-[11px] font-bold uppercase tracking-wider text-gray-500">Status Nilai</div>
                    <div class="col-span-2 text-right text-[11px] font-bold uppercase tracking-wider text-gray-500">Aksi</div>
                </div>

                <div class="divide-y divide-gray-100 bg-white">
                    @foreach($students as $student)
                        @php
                            $existingGrade = $gradeMap->get($student->id);
                            $scoreValue = old("scores.$student->id", $existingGrade->score ?? '');
                            $noteValue = old("notes.$student->id", $existingGrade->note ?? '');
                        @endphp
                        <div class="grade-row transition-all"
                             data-student-name="{{ strtolower($student->name) }}"
                             x-data="{ expanded: false, score: {{ Illuminate\Support\Js::from((string) $scoreValue) }} }"
                             x-show="matches({{ Illuminate\Support\Js::from(strtolower($student->name)) }})">
                            <button type="button"
                                    @click="expanded = !expanded"
                                    class="grid w-full grid-cols-[1fr_auto] items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-gray-50 sm:grid-cols-12 sm:px-5">
                                <div class="flex min-w-0 items-center gap-3 sm:col-span-7">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-xs font-black text-emerald-700">
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-bold text-gray-900">{{ $student->name }}</p>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ $student->nis ?? $student->nisn ?? '' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-2 sm:col-span-3 sm:justify-start">
                                    <template x-if="score !== '' && score !== null">
                                        <span class="rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-[11px] font-black text-emerald-700">
                                            <span x-text="score"></span> / {{ number_format((float) $currentMaxScore, 0) }}
                                        </span>
                                    </template>
                                    <template x-if="score === '' || score === null">
                                        <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-[11px] font-bold text-gray-400">Belum dinilai</span>
                                    </template>
                                </div>

                                <div class="hidden items-center justify-end sm:col-span-2 sm:flex">
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-gray-400">
                                        <span x-text="expanded ? 'Tutup' : 'Input'"></span>
                                        <svg class="h-4 w-4 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </span>
                                </div>
                            </button>

                            <div x-show="expanded" x-collapse x-cloak class="border-t border-gray-50 bg-gray-50/60 px-4 py-3 sm:px-5">
                                <div class="grid gap-3 sm:grid-cols-[180px_1fr] sm:items-end">
                                    <div class="space-y-1.5">
                                        <label class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Nilai</label>
                                        <input type="number" name="scores[{{ $student->id }}]" x-model="score" min="0" max="{{ $currentMaxScore }}" step="0.01"
                                               placeholder="Masukkan nilai"
                                               class="score-input w-full rounded-xl border-0 bg-white px-3 py-3 text-sm font-black text-gray-900 shadow-sm transition-all focus:ring-2 focus:ring-emerald-500 sm:py-2.5"
                                               @input="recountGraded()">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Keterangan</label>
                                        <input type="text" name="notes[{{ $student->id }}]" value="{{ $noteValue }}" maxlength="500"
                                               placeholder="Catatan nilai siswa..."
                                               class="w-full rounded-xl border-0 bg-white px-3 py-3 text-sm text-gray-700 shadow-sm placeholder:text-gray-400 transition-all focus:ring-2 focus:ring-emerald-500 sm:py-2.5">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div x-show="studentSearch.trim().length > 0 && filteredCount === 0" x-cloak class="rounded-2xl bg-white px-6 py-8 text-center sm:rounded-none">
                        <p class="text-sm font-bold text-gray-500">Siswa tidak ditemukan</p>
                        <p class="mt-1 text-xs text-gray-400">Coba kata kunci lain.</p>
                    </div>
                </div>
            @endif
        </section>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ $assignment ? route('guru.grades.show', $assignment) : route('guru.grades.index') }}"
               class="flex h-12 flex-1 items-center justify-center rounded-xl bg-gray-100 px-5 text-sm font-bold text-gray-600 transition-all hover:bg-gray-200 active:scale-[0.98] sm:flex-none">Batal</a>
            <button type="submit"
                    class="flex h-12 flex-2 items-center justify-center rounded-xl bg-emerald-600 px-6 text-sm font-bold text-white shadow-lg shadow-emerald-200 transition-all hover:bg-emerald-700 active:scale-[0.98] sm:flex-none">
                {{ $submitLabel }}
            </button>
        </div>
    </form>
</div>

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@push('scripts')
<script>
window.gradeForm = function(config) {
    return {
        studentSearch: '',
        maxScore: Number(config.maxScore || 100),
        studentCount: Number(config.studentCount || 0),
        gradedCount: Number(config.gradedCount || 0),
        studentNames: config.studentNames || [],
        formFilled: 0,
        formTotal: 6,

        get gradePct() {
            return this.studentCount > 0 ? Math.round((this.gradedCount / this.studentCount) * 100) : 0;
        },

        get filteredCount() {
            const q = this.studentSearch.trim().toLowerCase();
            if (!q) return this.studentCount;
            return this.studentNames.filter(name => name.includes(q)).length;
        },

        matches(name) {
            const q = this.studentSearch.trim().toLowerCase();
            return !q || String(name || '').includes(q);
        },

        init() {
            this.$nextTick(() => {
                this.recountGraded();
                this.recountForm();
                this.$el.querySelectorAll('.js-grade-track, #class_id, #subject_id').forEach(input => {
                    input.addEventListener('input', () => this.recountForm());
                    input.addEventListener('change', () => this.recountForm());
                });
            });
        },

        recountForm() {
            let count = ['class_id', 'subject_id'].filter(id => {
                const el = this.$el.querySelector('#' + id);
                return el && String(el.value || '').trim() !== '';
            }).length;

            this.$el.querySelectorAll('.js-grade-track').forEach(input => {
                if (String(input.value || '').trim() !== '') count++;
            });

            this.formFilled = Math.min(count, this.formTotal);
        },

        recountGraded() {
            let count = 0;
            this.$el.querySelectorAll('.score-input').forEach(input => {
                if (String(input.value || '').trim() !== '') count++;
            });
            this.gradedCount = count;
        }
    };
};

document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('class_id');
    const subjectSelect = document.getElementById('subject_id');
    const scheduleMap = @json($scheduleMap);
    const createUrl = '{{ route('guru.grades.create') }}';
    const isEdit = {{ $assignment ? 'true' : 'false' }};

    function filterSubjects() {
        if (!classSelect || !subjectSelect) return;
        const allowed = scheduleMap[classSelect.value] || [];
        Array.from(subjectSelect.options).forEach(function(option) {
            option.hidden = option.value && allowed.length > 0 && allowed.indexOf(Number(option.value)) === -1;
        });

        if (subjectSelect.value && allowed.length > 0 && allowed.indexOf(Number(subjectSelect.value)) === -1) {
            subjectSelect.value = '';
        }
    }

    if (classSelect) {
        classSelect.addEventListener('change', function() {
            filterSubjects();
            if (!isEdit && this.value) {
                window.location.href = createUrl + '?class_id=' + encodeURIComponent(this.value);
            }
        });
        filterSubjects();
    }
});
</script>
@endpush
