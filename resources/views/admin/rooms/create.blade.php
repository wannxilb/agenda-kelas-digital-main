@extends('layouts.admin')

@section('title', 'Tambah Ruangan')
@section('header', 'Tambah Ruangan Baru')

@section('content')
<div class="max-w-3xl mx-auto pb-12">
    <div class="mb-8">
        <nav class="flex text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.rooms.index') }}" class="hover:text-indigo-600 transition-colors">Ruangan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">Tambah Ruangan</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">Tambah Ruangan Baru</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.rooms.store') }}" method="POST">
            @csrf
            
            <div class="p-8 space-y-8">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Informasi Ruangan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Ruangan <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm @error('name') border-red-500 @enderror"
                                   placeholder="Contoh: Lab Komputer 1">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe Ruangan <span class="text-red-500">*</span></label>
                            <select name="type" id="type" required
                                    class="w-full text-sm @error('type') border-red-500 @enderror">
                                <option value="">Pilih Tipe Ruangan</option>
                                <option value="Laboratorium" {{ old('type') == 'Laboratorium' ? 'selected' : '' }}>Laboratorium</option>
                                <option value="Kelas" {{ old('type') == 'Kelas' ? 'selected' : '' }}>Kelas</option>
                            </select>
                            @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kapasitas (Siswa)</label>
                            <input type="number" name="capacity" value="{{ old('capacity') }}"
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm @error('capacity') border-red-500 @enderror"
                                   placeholder="Contoh: 30">
                            @error('capacity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.rooms.index') }}" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-100 transition-all">Batal</a>
                <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all shadow-sm">Simpan Ruangan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#type', {
            create: false,
            placeholder: 'Pilih Tipe Ruangan',
            maxOptions: null,
        });
    });
</script>
@endpush
@endsection