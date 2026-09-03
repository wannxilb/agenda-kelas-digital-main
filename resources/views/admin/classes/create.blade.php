{{-- resources/views/admin/classes/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Tambah Kelas')
@section('header', 'Tambah Kelas Baru')

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
@endphp

@section('content')
<div class="max-w-3xl mx-auto pb-12">
    <div class="mb-8">
        <nav class="flex text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.classes.index') }}" class="hover:text-indigo-600 transition-colors">Kelas</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">Tambah Kelas</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">Tambah Kelas Baru</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.classes.store') }}" method="POST">
            @csrf
            
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Kelas <span class="text-red-500">*</span></label>
                        <select name="name" id="name" required
                                class="w-full text-sm @error('name') border-red-500 @enderror">
                            <option value="">Pilih Nama Kelas</option>
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">Pilih jurusan dan tingkat untuk menampilkan nama kelas otomatis.</p>
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jurusan <span class="text-red-500">*</span></label>
                        <select name="major" id="major" required
                                class="w-full text-sm @error('major') border-red-500 @enderror">
                            <option value="">Pilih Jurusan</option>
                            @foreach($majors as $major)
                                <option value="{{ $major->name }}" {{ old('major') == $major->name ? 'selected' : '' }}>{{ $major->name }}</option>
                            @endforeach
                        </select>
                        @error('major') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tingkat Kelas <span class="text-red-500">*</span></label>
                        <select name="grade_level" id="grade_level" required
                                class="w-full text-sm @error('grade_level') border-red-500 @enderror">
                            <option value="">Pilih Tingkat</option>
                            <option value="X" {{ old('grade_level') == 'X' ? 'selected' : '' }}>Kelas X</option>
                            <option value="XI" {{ old('grade_level') == 'XI' ? 'selected' : '' }}>Kelas XI</option>
                            <option value="XII" {{ old('grade_level') == 'XII' ? 'selected' : '' }}>Kelas XII</option>
                        </select>
                        @error('grade_level') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Ajaran <span class="text-red-500">*</span></label>
                        <select name="academic_year" id="academic_year" required
                                class="w-full text-sm @error('academic_year') border-red-500 @enderror">
                            <option value="">Pilih Tahun Ajaran</option>
                            @foreach($academicYears as $yearName)
                                <option value="{{ $yearName }}" {{ old('academic_year') == $yearName ? 'selected' : '' }}>
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
                            <option value="{{ $teacher->id }}" {{ old('homeroom_teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-sm"
                              placeholder="Opsional...">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        <span class="ml-3 text-sm font-semibold text-gray-600">Aktifkan Kelas Sekarang</span>
                    </label>
                </div>
            </div>
            
            <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.classes.index') }}" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-100 transition-all">Batal</a>
                <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all shadow-sm">Simpan Kelas</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const classNames = {!! $classNames !!};

        const majorTom = new TomSelect('#major', {
            create: false,
            placeholder: 'Pilih Jurusan...',
            maxOptions: null,
        });
        const gradeTom = new TomSelect('#grade_level', {
            create: false,
            placeholder: 'Pilih Tingkat',
            maxOptions: null,
        });
        const nameTom = new TomSelect('#name', {
            create: false,
            placeholder: 'Pilih Nama Kelas',
            maxOptions: null,
            onFocus: function() {
                if (nameTom.getValue() === '') {
                    nameTom.open();
                }
            },
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

        function updateNames() {
            const major = majorTom.getValue();
            const grade = gradeTom.getValue();

            nameTom.clearOptions();
            nameTom.clear(true);

            if (!major || !grade) {
                nameTom.refreshOptions(false);
                return;
            }

            const byGrade = (classNames[major] || {});
            const names = byGrade[grade] || [];

            names.forEach(function(name) {
                nameTom.addOption({ value: name, text: name });
            });
            nameTom.refreshOptions(false);

            if (names.length === 0) {
                return;
            }

            const current = '{{ old('name') }}';
            if (current && names.indexOf(current) !== -1) {
                nameTom.setValue(current);
            } else {
                nameTom.clear(true);
            }
        }

        majorTom.on('change', updateNames);
        gradeTom.on('change', updateNames);
        updateNames();
    });
</script>
@endpush
@endsection
