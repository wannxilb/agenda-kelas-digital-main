{{-- resources/views/admin/classes/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Kelas')
@section('header', 'Edit Kelas')

@php
    $classNames = json_encode(collect($majors)->mapWithKeys(function ($m) {
        $data = [];
        foreach ($m->gradeTemplates as $t) {
            $names = [];
            for ($i = 1; $i <= $t->rombel_count; $i++) {
                $names[] = $t->rombel_count > 1 ? "{$t->grade_level} {$t->code} {$i}" : "{$t->grade_level} {$t->code}";
            }
            $data[$t->grade_level] = $names;
        }
        return [$m->name => $data];
    })->all(), JSON_UNESCAPED_UNICODE);
    $currentName = old('name', $class->name);
    $currentMajor = old('major', $class->major);
    $currentGrade = old('grade_level', $class->grade_level);
@endphp

@section('content')
<div class="max-w-3xl mx-auto pb-12">
    <div class="mb-8">
        <nav class="flex text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.classes.index') }}" class="hover:text-indigo-600 transition-colors">Kelas</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">Edit Kelas</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">Edit Detail Kelas</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.classes.update', $class) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Kelas <span class="text-red-500">*</span></label>
                        <select name="name" id="name" required
                                class="w-full text-sm @error('name') border-red-500 @enderror">
                            <option value="{{ $currentName }}" selected>{{ $currentName }}</option>
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">Nama kelas otomatis mengikuti jurusan dan tingkat.</p>
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jurusan <span class="text-red-500">*</span></label>
                        <select name="major" id="major" required
                                class="w-full text-sm @error('major') border-red-500 @enderror">
                            <option value="">Pilih Jurusan</option>
                            @foreach($majors as $major)
                                <option value="{{ $major->name }}" {{ $currentMajor == $major->name ? 'selected' : '' }}>{{ $major->name }}</option>
                            @endforeach
                        </select>
                        @error('major') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tingkat Kelas <span class="text-red-500">*</span></label>
                        <select name="grade_level" id="grade_level" required
                                class="w-full text-sm @error('grade_level') border-red-500 @enderror">
                            <option value="">Pilih Tingkat</option>
                            <option value="X" {{ $currentGrade == 'X' ? 'selected' : '' }}>Kelas X</option>
                            <option value="XI" {{ $currentGrade == 'XI' ? 'selected' : '' }}>Kelas XI</option>
                            <option value="XII" {{ $currentGrade == 'XII' ? 'selected' : '' }}>Kelas XII</option>
                        </select>
                        @error('grade_level') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Ajaran <span class="text-red-500">*</span></label>
                        <select name="academic_year" id="academic_year" required
                                class="w-full text-sm @error('academic_year') border-red-500 @enderror">
                            <option value="">Pilih Tahun Ajaran</option>
                            @foreach($academicYears as $yearName)
                                <option value="{{ $yearName }}" {{ old('academic_year', $class->academic_year) == $yearName ? 'selected' : '' }}>
                                    {{ $yearName }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Wali Kelas</label>
                    <select name="homeroom_teacher_id" id="homeroom_teacher_id"
                            class="w-full text-sm">
                        <option value="">Pilih Wali Kelas</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('homeroom_teacher_id', $class->homeroom_teacher_id) == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-sm"
                              placeholder="Opsional...">{{ old('description', $class->description) }}</textarea>
                </div>

                <div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $class->is_active) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        <span class="ml-3 text-sm font-semibold text-gray-600">Aktifkan Kelas</span>
                    </label>
                </div>

                @if($class->created_at)
                <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-100 flex flex-col md:flex-row justify-between text-sm text-blue-800">
                    <div><span class="font-semibold">Dibuat:</span> {{ $class->created_at->format('d/m/Y H:i') }}</div>
                    <div><span class="font-semibold">Diperbarui:</span> {{ $class->updated_at->format('d/m/Y H:i') }}</div>
                </div>
                @endif
            </div>
            
            <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.classes.index') }}" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-100 transition-all">Batal</a>
                <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const classNames = {!! $classNames !!};
        const majorSelect = document.getElementById('major');
        const gradeSelect = document.getElementById('grade_level');
        const nameSelect = document.getElementById('name');
        const currentName = '{{ $currentName }}';

        function updateNames() {
            const major = majorSelect.value;
            const grade = gradeSelect.value;

            nameSelect.innerHTML = '';
            nameSelect.disabled = false;

            if (!major || !grade) {
                nameSelect.disabled = true;
                nameSelect.innerHTML = '<option value="">Pilih jurusan dan tingkat terlebih dahulu</option>';
                return;
            }

            const byGrade = (classNames[major] || {});
            const names = (byGrade[grade] || []).slice();

            // Pastikan nama kelas saat ini tetap tersedia jika tidak ada di opsi generated
            if (currentName && names.indexOf(currentName) === -1) {
                names.unshift(currentName);
            }

            if (names.length === 0) {
                nameSelect.disabled = true;
                nameSelect.innerHTML = '<option value="">Tidak ada opsi nama kelas</option>';
                return;
            }

            names.forEach(function(name) {
                const opt = document.createElement('option');
                opt.value = name;
                opt.textContent = name;
                opt.selected = (name === currentName);
                nameSelect.appendChild(opt);
            });
        }

        majorSelect.addEventListener('change', updateNames);
        gradeSelect.addEventListener('change', updateNames);
        updateNames();

        new TomSelect('#major', {
            create: false,
            placeholder: 'Cari Jurusan...',
            maxOptions: null,
        });
        new TomSelect('#grade_level', {
            create: false,
            placeholder: 'Pilih Tingkat',
            maxOptions: null,
        });
        new TomSelect('#homeroom_teacher_id', {
            create: false,
            placeholder: 'Cari Wali Kelas...',
            sortField: { field: 'text', direction: 'asc' },
            maxOptions: null,
        });
        new TomSelect('#academic_year', {
            create: false,
            placeholder: 'Pilih Tahun Ajaran',
            maxOptions: null,
        });
    });
</script>
@endpush
@endsection