{{-- resources/views/admin/subjects/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Mata Pelajaran')
@section('header', 'Edit Data Mata Pelajaran')

@section('content')
<div class="max-w-3xl mx-auto pb-12">
    <div class="mb-8">
        <nav class="flex text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.subjects.index') }}" class="hover:text-indigo-600 transition-colors">Mata Pelajaran</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">Edit Mata Pelajaran</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">Edit Data Mata Pelajaran</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.subjects.update', $subject) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="p-8 space-y-8">
                <!-- SECTION 1: Informasi Mata Pelajaran -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        Informasi Mata Pelajaran
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Mata Pelajaran <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $subject->name) }}" required
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm @error('name') border-red-500 @enderror">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jam Pelajaran Per Minggu <span class="text-red-500">*</span></label>
                            <select name="credit_hours" id="credit_hours" required
                                    class="w-full text-sm @error('credit_hours') border-red-500 @enderror">
                                @for($i = 1; $i <= 8; $i++)
                                    <option value="{{ $i }}" {{ old('credit_hours', $subject->credit_hours) == $i ? 'selected' : '' }}>{{ $i }} JP</option>
                                @endfor
                            </select>
                            @error('credit_hours') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            <p class="text-[10px] text-gray-500 mt-1">Pilih total jam pelajaran (JP) per minggu.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Guru Pengampu Saat Ini</label>
                            <div class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700">
                                {{ $subject->teachers->pluck('name')->implode(', ') ?: 'Belum ada guru' }}
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">Guru dapat dikelola di halaman detail mata pelajaran.</p>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: Deskripsi -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Deskripsi
                    </h3>
                    <textarea name="description" rows="4"
                              class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-sm @error('description') border-red-500 @enderror">{{ old('description', $subject->description) }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                @if($subject->created_at)
                <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-500 border border-gray-100 flex flex-col md:flex-row justify-between">
                    <div><span class="font-semibold text-gray-700">Terdaftar:</span> {{ $subject->created_at->format('d M Y, H:i') }}</div>
                    <div><span class="font-semibold text-gray-700">Diperbarui:</span> {{ $subject->updated_at->format('d M Y, H:i') }}</div>
                </div>
                @endif
            </div>
            
            <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.subjects.index') }}" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-100 transition-all">Batal</a>
                <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#credit_hours', {
            create: false,
            placeholder: 'Pilih JP',
            maxOptions: null,
        });
    });
</script>
@endpush
@endsection