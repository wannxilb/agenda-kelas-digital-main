{{-- resources/views/sekretaris/agenda/create.blade.php --}}
@extends('layouts.sekretaris')

@section('title', 'Buat Agenda Baru')
@section('header', 'Tambah Agenda Kelas')

@section('content')
<div class="max-w-5xl mx-auto pb-24 sm:pb-12 px-2 sm:px-0">
    <div class="mb-6 sm:mb-8 flex flex-col sm:block mt-2 sm:mt-0 px-2 sm:px-0">
        <div class="flex items-center gap-3 sm:hidden mb-1">
            <a href="{{ route('sekretaris.agenda.index') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-600 active:bg-gray-50 active:scale-95 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h1 class="text-xl font-black text-gray-900 tracking-tight">Buat Agenda Baru</h1>
        </div>

        <nav class="hidden sm:flex mb-4 text-xs font-bold uppercase tracking-widest text-gray-400">
            <ol class="flex items-center space-x-2">
                <li><a href="{{ route('sekretaris.dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a></li>
                <li><svg class="w-3 h-3 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></li>
                <li><a href="{{ route('sekretaris.agenda.index') }}" class="hover:text-indigo-600 transition-colors">Agenda</a></li>
                <li><svg class="w-3 h-3 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></li>
                <li class="text-indigo-600">Tambah Baru</li>
            </ol>
        </nav>
        <h1 class="hidden sm:block text-3xl font-black text-gray-900 tracking-tight">Buat Agenda Baru</h1>
        <p class="hidden sm:block text-gray-500 mt-1 font-medium text-sm">Lengkapi formulir di bawah untuk mencatat agenda kegiatan kelas hari ini.</p>
    </div>

    <form id="agendaForm" action="{{ route('sekretaris.agenda.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        <input type="hidden" name="class_id" value="{{ $selectedClassId ?? '' }}">
        @include('partials.agenda-location-fields')
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-5 sm:px-8 py-4 sm:py-5 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Informasi Utama Agenda
                </h3>
            </div>
            
            <div class="p-5 sm:p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @php
                        $dateBorderClass = $errors->has('date') ? 'border-red-500' : 'border-gray-200';
                        $titleBorderClass = $errors->has('title') ? 'border-red-500' : 'border-gray-200';
                    @endphp
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Kegiatan <span class="text-red-500">*</span></label>
                        <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required
                               class="w-full px-4 py-2.5 bg-white border {{ $dateBorderClass }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm">
                        @error('date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Agenda / Materi Pembelajaran <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               class="w-full px-4 py-2.5 bg-white border {{ $titleBorderClass }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                               placeholder="Contoh: Pembahasan Logaritma Bab 2">
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kelas Anda</label>
                        <input type="text" value="{{ optional($classes->first())->name ?? 'Tidak ada kelas' }}" disabled
                               class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-sm text-gray-500 cursor-not-allowed">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Mata Pelajaran</label>
                        <select name="subject_id" id="subject_id" class="w-full text-sm">
                            <option value="">Pilih Mapel (Opsional)</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}"
                                    data-teacher="{{ $subject->teachers->first()->id ?? '' }}"
                                    data-teacher-name="{{ $subject->teachers->first()->name ?? '' }}"
                                    {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Ruangan <span class="text-red-500">*</span></label>
                        <select name="room" id="room" required
                                class="w-full text-sm @error('room') border-red-500 @enderror">
                            <option value="">Memuat pilihan ruangan...</option>
                            @foreach($scheduleRooms as $r)
                                <option value="{{ $r }}" {{ old('room') == $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                            @if($scheduleRooms->isEmpty())
                                @foreach($allRooms as $r)
                                    <option value="{{ $r }}" {{ old('room') == $r ? 'selected' : '' }}>{{ $r }}</option>
                                @endforeach
                            @endif
                        </select>
                        <p id="room-hint" class="text-[10px] font-semibold text-indigo-500 mt-1 hidden">
                            <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Menampilkan ruangan sesuai jadwal hari ini
                        </p>
                        <p id="room-fallback-hint" class="text-[10px] font-semibold text-amber-500 mt-1 hidden">
                            <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5C2.57 18.333 3.532 20 5.072 20z"></path></svg>
                            Tidak ada jadwal pelajaran pada hari ini. Menampilkan semua ruangan.
                        </p>
                        @error('room') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Guru Pengampu <span class="text-red-500">*</span></label>
                        <select name="teacher_id" id="teacher_id" required
                                class="w-full text-sm @error('teacher_id') border-red-500 @enderror">
                            <option value="">Pilih Guru</option>
                            @foreach($scheduleTeachers as $t)
                                <option value="{{ $t['id'] }}" {{ old('teacher_id') == $t['id'] ? 'selected' : '' }}>
                                    {{ $t['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('teacher_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 sm:px-8 py-4 sm:py-5 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Rincian Materi &amp; Kegiatan
                </h3>
            </div>
            <div class="p-5 sm:p-8 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi Kegiatan / Catatan Guru <span class="text-red-500">*</span></label>
                    <div class="rounded-xl border border-gray-200 overflow-hidden bg-white">
                        <div id="editor" class="min-h-37.5 sm:min-h-62.5 bg-white"></div>
                    </div>
                    <textarea name="description" id="description" class="hidden"></textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Lampiran Dokumen (Opsional)</label>
                    <div class="relative group">
                        <input type="file" name="attachment" id="attachment" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept=".pdf,.doc,.docx,.jpg,.png,.xlsx">
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center group-hover:border-indigo-500 group-hover:bg-indigo-50 transition-all duration-300">
                            <div class="w-14 h-14 bg-gray-50 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 group-hover:bg-white transition-all shadow-sm">
                                <svg class="w-7 h-7 text-gray-400 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            </div>
                            <p class="text-sm font-bold text-gray-900" id="fileNameText">Klik atau Seret File ke Sini</p>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-2">PDF, DOC, JPG, PNG, XLSX (Max 2MB)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-3 sm:p-6 flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="flex p-1 bg-gray-100 rounded-xl w-full md:w-auto">
                <label class="flex-1 text-center cursor-pointer relative group">
                    <input type="radio" name="status" value="published" {{ old('status', 'published') == 'published' ? 'checked' : '' }} class="peer sr-only">
                    <div class="py-2 px-4 text-xs font-bold rounded-lg text-gray-500 peer-checked:bg-white peer-checked:text-indigo-600 peer-checked:shadow-sm transition-all group-active:scale-95">Published</div>
                </label>
                <label class="flex-1 text-center cursor-pointer relative group">
                    <input type="radio" name="status" value="draft" {{ old('status') == 'draft' ? 'checked' : '' }} class="peer sr-only">
                    <div class="py-2 px-4 text-xs font-bold rounded-lg text-gray-500 peer-checked:bg-white peer-checked:text-gray-900 peer-checked:shadow-sm transition-all group-active:scale-95">Draft</div>
                </label>
            </div>
            
            <div class="flex items-center gap-2.5 w-full md:w-auto">
                <a href="{{ route('sekretaris.agenda.index') }}" 
                   class="flex-1 md:flex-none px-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-100 active:scale-95 transition-all text-center">Batal</a>
                <button type="submit"
                        class="flex-2 md:flex-none px-6 py-2.5 text-white bg-indigo-600 rounded-xl text-sm font-bold hover:bg-indigo-700 active:scale-95 transition-all shadow-md shadow-indigo-200 text-center">
                    Simpan Agenda
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Tuliskan rincian kegiatan atau materi yang dibahas...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'blockquote', 'code-block'],
                ['clean']
            ]
        }
    });
    
    @if(old('description'))
        quill.root.innerHTML = {!! json_encode(old('description')) !!};
    @endif
    
    document.getElementById('agendaForm').onsubmit = function() {
        var content = quill.root.innerHTML;
        if (content === '<p><br></p>') content = '';
        document.querySelector('#description').value = content;
        return true;
    };

    // ── TomSelect ──────────────────────────────────────────────────────────
    const subjectTom = new TomSelect('#subject_id', {
        create: false,
        placeholder: 'Cari Mata Pelajaran...',
        sortField: false,
        maxOptions: null,
    });
    const roomTom = new TomSelect('#room', {
        create: false,
        placeholder: 'Pilih Ruangan',
        maxOptions: null,
    });
    const teacherTom = new TomSelect('#teacher_id', {
        create: false,
        placeholder: 'Cari Guru...',
        sortField: { field: 'text', direction: 'asc' },
        maxOptions: null,
    });

    // ── Schedule Data ──────────────────────────────────────────────────────
    const API_URL = '{{ route("sekretaris.agenda.get-schedule-info") }}';
    const INITIAL_SCHEDULE_DATA = @json($scheduleData);
    let subjectTeacherMap = {};

    // Populate initial map
    (function() {
        Object.keys(INITIAL_SCHEDULE_DATA).forEach(function(key) {
            subjectTeacherMap[key] = INITIAL_SCHEDULE_DATA[key];
        });
    })();

    function applySubjectFilter(subjectId) {
        if (!subjectId) return;
        var info = subjectTeacherMap[subjectId];
        if (info) {
            if (info.teacher_id) {
                teacherTom.setValue(String(info.teacher_id));
            }
            if (info.room) {
                roomTom.setValue(info.room);
            }
            return;
        }
        var opt = document.querySelector('#subject_id option[value="' + subjectId + '"]');
        if (opt) {
            var fallbackTeacherId = opt.getAttribute('data-teacher');
            if (fallbackTeacherId) {
                teacherTom.setValue(String(fallbackTeacherId));
            }
        }
    }

    function rebuildSelect(tom, options) {
        tom.close();
        tom.clear(true);
        tom.clearOptions();
        options.forEach(function(opt) { tom.addOption(opt); });
        tom.refreshOptions(false);
        tom.close();
    }

    function fetchScheduleInfo(date) {
        if (!date) return;

        subjectTom.off('change');

        const params = new URLSearchParams({ date: date });
        @if(isset($selectedClassId) && $selectedClassId)
        params.set('class_id', '{{ $selectedClassId }}');
        @endif

        fetch(API_URL + '?' + params.toString())
            .then(function(r) { return r.json(); })
            .then(function(data) {
                subjectTeacherMap = {};
                data.subjects.forEach(function(s) {
                    subjectTeacherMap[s.id] = {
                        teacher_id: s.teacher_id,
                        teacher_name: s.teacher,
                        room: s.room
                    };
                });

                var subjOpts = data.subjects.map(function(s) { return { value: String(s.id), text: s.name }; });
                var roomOpts = data.rooms.map(function(r) { return { value: r, text: r }; });
                var teacherOpts = data.teachers.map(function(t) { return { value: String(t.id), text: t.name }; });

                rebuildSelect(subjectTom, subjOpts);
                rebuildSelect(roomTom, roomOpts);
                rebuildSelect(teacherTom, teacherOpts);

                var hint = document.getElementById('room-hint');
                var fallback = document.getElementById('room-fallback-hint');
                if (data.has_schedule) {
                    hint.classList.remove('hidden');
                    fallback.classList.add('hidden');
                } else {
                    hint.classList.add('hidden');
                    fallback.classList.remove('hidden');
                }

                subjectTom.on('change', onSubjectChange);
            })
            .catch(function() {
                subjectTom.on('change', onSubjectChange);
            });
    }

    function onSubjectChange(value) {
        applySubjectFilter(value);
    }

    document.getElementById('date').addEventListener('change', function() {
        fetchScheduleInfo(this.value);
    });

    subjectTom.on('change', onSubjectChange);

    // Apply initial filter for pre-selected subject
    var initialSubjectValue = subjectTom.getValue();
    if (initialSubjectValue) {
        applySubjectFilter(initialSubjectValue);
    }

    document.getElementById('attachment').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            document.getElementById('fileNameText').textContent = file.name;
            document.getElementById('fileNameText').classList.add('text-indigo-600');
        }
    });
});
</script>
@endpush
@endsection
