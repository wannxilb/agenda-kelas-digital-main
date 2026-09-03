{{-- resources/views/admin/academic_records/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Riwayat Akademik')
@section('header', 'Riwayat Akademik')

@section('content')
<div class="pb-12">
    <!-- Filter Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
        <form method="GET" action="{{ route('admin.academic-records.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Tahun Ajaran</label>
                <select name="academic_year_name" class="block w-full px-4 py-2 border border-gray-200 bg-gray-50 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all text-sm font-medium">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->name }}" {{ $selectedYearName == $year->name ? 'selected' : '' }}>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Filter Tingkatan</label>
                <select name="grade_level" class="block w-full px-4 py-2 border border-gray-200 bg-gray-50 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all text-sm font-medium">
                    <option value="">-- Semua Tingkat --</option>
                    <option value="X" {{ ($searchGrade ?? '') == 'X' ? 'selected' : '' }}>X</option>
                    <option value="XI" {{ ($searchGrade ?? '') == 'XI' ? 'selected' : '' }}>XI</option>
                    <option value="XII" {{ ($searchGrade ?? '') == 'XII' ? 'selected' : '' }}>XII</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-2 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-md shadow-indigo-200">
                    Filter Riwayat
                </button>
            </div>
        </form>
    </div>

    <h2 class="text-xl font-bold text-gray-800 mb-6">
        Daftar Kelas Tahun Ajaran: <span class="text-indigo-600">{{ $selectedYearName }}</span>
    </h2>

    <!-- Class Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($classes as $class)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">{{ $class->name }}</h3>
                <p class="text-sm text-gray-600 mt-1">Wali Kelas: {{ $class->classHistories->first()?->homeroomTeacher?->name ?? 'Belum ada' }}</p>
                <p class="text-xs text-gray-400 mt-1">Tahun Masuk / Angkatan: <span class="font-semibold">{{ $class->academic_year ?? '-' }}</span></p>
            </div>
            <a href="{{ route('admin.academic-records.show', ['classId' => $class->id, 'yearName' => $selectedYearName]) }}" 
               class="mt-6 block text-center py-2 bg-indigo-50 text-indigo-700 font-bold rounded-xl hover:bg-indigo-100 transition-all">
                Lihat Daftar Siswa
            </a>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-gray-400">
            Tidak ada data kelas untuk tahun ajaran {{ $selectedYearName }}.
        </div>
        @endforelse
    </div>
</div>
@endsection
