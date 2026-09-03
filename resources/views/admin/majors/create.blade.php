@extends('layouts.admin')

@section('title', 'Tambah Jurusan')
@section('header', 'Tambah Jurusan Baru')

@section('content')
<div class="max-w-3xl mx-auto pb-12">
    <div class="mb-8">
        <nav class="flex text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.majors.index') }}" class="hover:text-indigo-600 transition-colors">Jurusan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">Tambah Jurusan</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">Tambah Jurusan Baru</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.majors.store') }}" method="POST">
            @csrf

            <div class="p-8 space-y-8">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">
                        Informasi Jurusan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Jurusan <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm @error('name') border-red-500 @enderror"
                                   placeholder="Contoh: Rekayasa Perangkat Lunak">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kode Jurusan</label>
                            <input type="text" name="code" value="{{ old('code') }}"
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm @error('code') border-red-500 @enderror"
                                   placeholder="Contoh: RPL">
                            @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">
                        Konfigurasi Rombel per Tingkat
                    </h3>
                    <div class="space-y-4">
                        @foreach(['X' => 'Kelas X', 'XI' => 'Kelas XI', 'XII' => 'Kelas XII'] as $gradeKey => $gradeLabel)
                            @php
                                $oldCode = old("grades.$gradeKey.code");
                                $oldCount = old("grades.$gradeKey.rombel_count");
                            @endphp
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $gradeLabel }} — Kode Kelas <span class="text-red-500">*</span></label>
                                    <input type="text" name="grades[{{ $gradeKey }}][code]" value="{{ $oldCode }}"
                                           class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm @error('grades.'.$gradeKey.'.code') border-red-500 @enderror"
                                           placeholder="Contoh: PPLG">
                                    @error('grades.'.$gradeKey.'.code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah Rombel <span class="text-red-500">*</span></label>
                                    <input type="number" name="grades[{{ $gradeKey }}][rombel_count]" value="{{ $oldCount }}"
                                           min="1" max="20" required
                                           class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm @error('grades.'.$gradeKey.'.rombel_count') border-red-500 @enderror">
                                    @error('grades.'.$gradeKey.'.rombel_count') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        <span class="ml-3 text-sm font-semibold text-gray-600">Aktifkan Jurusan Ini</span>
                    </label>
                </div>
            </div>

            <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.majors.index') }}" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-100 transition-all">Batal</a>
                <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all shadow-sm">Simpan Jurusan</button>
            </div>
        </form>
    </div>
</div>
@endsection
