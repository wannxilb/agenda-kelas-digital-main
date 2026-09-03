{{-- resources/views/admin/schedules/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Tambah Jadwal')
@section('header', 'Tambah Jadwal Baru')

@section('content')
<div class="max-w-3xl mx-auto pb-12">
    <!-- Breadcrumb & Header -->
    <div class="mb-8">
        <nav class="flex text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.schedules.index') }}" class="hover:text-indigo-600 transition-colors">Jadwal Pelajaran</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">Tambah Jadwal</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">Tambah Jadwal Baru</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.schedules.store') }}" method="POST">
            @csrf
            
            <div class="p-8 space-y-6">
                <!-- Informasi Jadwal -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kelas -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kelas <span class="text-red-500">*</span></label>
                        <select name="class_id" id="class_id" required
                                class="w-full text-sm @error('class_id') border-red-500 @enderror">
                            <option value="">Pilih Kelas</option>
                            @foreach($classList as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} ({{ $class->grade_level }})
                                </option>
                            @endforeach
                        </select>
                        @error('class_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Hari <span class="text-red-500">*</span></label>
                        <select name="day" id="day" required
                                class="w-full text-sm @error('day') border-red-500 @enderror">
                            <option value="">Pilih Hari</option>
                            <option value="Monday" {{ old('day') == 'Monday' ? 'selected' : '' }}>Senin</option>
                            <option value="Tuesday" {{ old('day') == 'Tuesday' ? 'selected' : '' }}>Selasa</option>
                            <option value="Wednesday" {{ old('day') == 'Wednesday' ? 'selected' : '' }}>Rabu</option>
                            <option value="Thursday" {{ old('day') == 'Thursday' ? 'selected' : '' }}>Kamis</option>
                            <option value="Friday" {{ old('day') == 'Friday' ? 'selected' : '' }}>Jumat</option>
                        </select>
                        @error('day') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <!-- Tipe Minggu -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe Minggu <span class="text-red-500">*</span></label>
                        <select name="week_type" id="week_type" required
                                class="w-full text-sm @error('week_type') border-red-500 @enderror">
                            <option value="semua" {{ old('week_type') == 'semua' ? 'selected' : '' }}>Semua Minggu (Tiap Minggu)</option>
                            <option value="ganjil" {{ old('week_type') == 'ganjil' ? 'selected' : '' }}>Minggu Ganjil (Ke-1, 3, dst)</option>
                            <option value="genap" {{ old('week_type') == 'genap' ? 'selected' : '' }}>Minggu Genap (Ke-2, 4, dst)</option>
                        </select>
                        @error('week_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <!-- Jam Mulai & Jam Selesai -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jam Mulai <span class="text-red-500">*</span></label>
                        <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" required
                               class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm @error('start_time') border-red-500 @enderror">
                        @error('start_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jam Selesai <span class="text-red-500">*</span></label>
                        <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}" required
                               class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm @error('end_time') border-red-500 @enderror">
                        @error('end_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Jumlah JP -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah JP <span class="text-red-500">*</span></label>
                        <input type="number" name="jumlah_jp" id="jumlah_jp" min="1" value="{{ old('jumlah_jp', 2) }}" required
                               class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm @error('jumlah_jp') border-red-500 @enderror">
                        <p class="text-[11px] text-gray-400 mt-1">Durasi otomatis = Jumlah JP × 45 menit (1 JP = 45 menit)</p>
                        @error('jumlah_jp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Mata Pelajaran -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Mata Pelajaran <span class="text-red-500">*</span></label>
                        <select name="subject_id" id="subject_id" required
                                class="w-full text-sm @error('subject_id') border-red-500 @enderror">
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }} ({{ $subject->credit_hours }} JP)
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <!-- Guru Pengampu -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Guru Pengampu <span class="text-red-500">*</span></label>
                        <select name="teacher_id" id="teacher_id" required
                                class="w-full text-sm @error('teacher_id') border-red-500 @enderror">
                            <option value="">Pilih Guru</option>
                        </select>
                        @error('teacher_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <!-- Ruangan -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Ruangan <span class="text-red-500">*</span></label>
                        <select name="room_id" id="room_id" required
                                class="w-full text-sm @error('room_id') border-red-500 @enderror">
                            <option value="">Pilih Ruangan</option>
                        </select>
                        @error('room_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                
                <!-- Catatan -->
                <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-100 flex gap-3">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-bold mb-1">Catatan Penting:</p>
                        <ul class="list-disc list-inside text-xs space-y-1 text-blue-700/80">
                            <li>Pastikan tidak ada jadwal yang bentrok di kelas yang sama pada jam yang sama.</li>
                            <li>Guru pengampu akan otomatis terisi berdasarkan mata pelajaran yang dipilih.</li>
                            <li>Ruangan akan otomatis difilter berdasarkan ketersediaan pada waktu yang dipilih.</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.schedules.index') }}" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-100 transition-all">Batal</a>
                <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all shadow-sm">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const subjectsData = {!! json_encode($subjectJson) !!};
        const JP_PER_SESSION = 45;

        const startTimeInput = document.querySelector('input[name="start_time"]');
        const endTimeInput = document.querySelector('input[name="end_time"]');
        const jpInput = document.getElementById('jumlah_jp');

        function recalcEndTime() {
            const start = startTimeInput.value;
            const jp = parseInt(jpInput.value, 10);
            if (!start || isNaN(jp) || jp < 1) return;
            const [h, m] = start.split(':').map(Number);
            const totalMinutes = h * 60 + m + jp * JP_PER_SESSION;
            const endH = Math.floor(totalMinutes / 60) % 24;
            const endM = totalMinutes % 60;
            endTimeInput.value = String(endH).padStart(2, '0') + ':' + String(endM).padStart(2, '0');
        }

        const classTom = new TomSelect('#class_id', {
            create: false,
            sortField: { field: 'text', direction: 'asc' },
            placeholder: 'Pilih Kelas',
            maxOptions: null,
        });

        const dayTom = new TomSelect('#day', {
            create: false,
            placeholder: 'Pilih Hari',
            maxOptions: null,
        });

        const weekTypeTom = new TomSelect('#week_type', {
            create: false,
            placeholder: 'Pilih Tipe Minggu',
            maxOptions: null,
        });

        const subjectTom = new TomSelect('#subject_id', {
            create: false,
            sortField: { field: 'text', direction: 'asc' },
            placeholder: 'Cari Mata Pelajaran...',
            maxOptions: null,
            options: subjectsData.map(function(s) {
                return {
                    value: s.id,
                    text: s.name + ' (' + s.credit_hours + ' JP)',
                    name: s.name,
                    credit_hours: s.credit_hours,
                    teachers: s.teachers,
                };
            }),
            render: {
                option: function(data, escape) {
                    const teacherCount = data.teachers ? data.teachers.length : 0;
                    const teacherInfo = teacherCount > 0 ? teacherCount + ' guru' : 'Belum Ditentukan';
                    return '<div class="py-1 px-2">' + escape(data.name) + ' <span class="text-xs text-gray-400">(' + data.credit_hours + ' JP) - ' + teacherInfo + '</span></div>';
                },
                item: function(data, escape) {
                    return '<div>' + escape(data.name) + ' (' + data.credit_hours + ' JP)</div>';
                }
            },
            onChange: function(value) {
                updateTeacherSelect(value);
                if (value && subjectTom.options[value]) {
                    jpInput.value = subjectTom.options[value].credit_hours || 1;
                    recalcEndTime();
                }
            }
        });

        const teacherTom = new TomSelect('#teacher_id', {
            create: false,
            sortField: { field: 'text', direction: 'asc' },
            placeholder: 'Pilih Guru',
            maxOptions: null,
        });

        function updateTeacherSelect(subjectId) {
            teacherTom.clear();
            teacherTom.clearOptions();

            if (subjectId && subjectTom.options[subjectId]) {
                const teachers = subjectTom.options[subjectId].teachers ?? [];
                if (teachers.length > 0) {
                    teachers.forEach(teacher => {
                        teacherTom.addOption({
                            value: teacher.id,
                            text: `${teacher.name} (${teacher.nip})`
                        });
                    });
                } else {
                    teacherTom.addOption({ value: '', text: 'Tidak ada guru untuk mapel ini' });
                }
                teacherTom.refreshOptions(false);
            }
        }

        let roomsLoaded = false;

        const roomTom = new TomSelect('#room_id', {
            create: false,
            placeholder: 'Pilih Hari dan Jam Terlebih Dahulu',
            maxOptions: null,
            onFocus: function() {
                loadRooms();
            },
        });

        async function loadRooms() {
            if (roomsLoaded) return;

            const startTimeInput = document.querySelector('input[name="start_time"]');
            const endTimeInput = document.querySelector('input[name="end_time"]');
            const day = dayTom.getValue();
            const start = startTimeInput.value;
            const end = endTimeInput.value;

            if (!day || !start || !end) {
                roomTom.clear();
                roomTom.clearOptions();
                roomTom.addOption({ value: '', text: 'Pilih Hari dan Jam Terlebih Dahulu' });
                roomTom.refreshOptions(false);
                return;
            }

            try {
                const response = await fetch(`{{ route('admin.schedules.get-available-rooms') }}?day=${day}&start_time=${start}&end_time=${end}`);
                const rooms = await response.json();

                roomTom.clear();
                roomTom.clearOptions();
                roomTom.addOption({ value: '', text: 'Pilih Ruangan' });
                rooms.forEach(room => {
                    roomTom.addOption({ value: room.id, text: room.name });
                });
                roomTom.refreshOptions(false);
                roomsLoaded = true;
            } catch (error) {
                roomTom.clear();
                roomTom.clearOptions();
                roomTom.addOption({ value: '', text: 'Gagal memuat ruangan' });
                roomTom.refreshOptions(false);
            }
        }

        // Tandai rooms perlu refresh ulang saat hari berubah (tanpa sentuh DOM)
        dayTom.on('change', function() {
            roomsLoaded = false;
        });

        startTimeInput.addEventListener('change', function() {
            roomsLoaded = false;
            recalcEndTime();
        });
        jpInput.addEventListener('input', function() {
            recalcEndTime();
        });


    });
</script>
@endpush
@endsection