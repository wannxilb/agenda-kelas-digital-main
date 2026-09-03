{{-- resources/views/admin/teachers/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Guru')
@section('header', 'Edit Data Guru')

@section('content')
<div class="max-w-3xl mx-auto pb-12">
    <div class="mb-8">
        <nav class="flex text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.teachers.index') }}" class="hover:text-indigo-600 transition-colors">Guru</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">Edit Guru</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">Edit Data Guru</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.teachers.update', $teacher) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="p-8 space-y-8">
                <!-- SECTION 1: Informasi Pribadi -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Informasi Pribadi & Kontak
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $teacher->name) }}" required
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm @error('name') border-red-500 @enderror">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">NIP (Nomor Induk Pegawai) <span class="text-red-500">*</span></label>
                            <input type="text" name="nip" value="{{ old('nip', $teacher->nip) }}" required
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm @error('nip') border-red-500 @enderror">
                            @error('nip') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $teacher->email) }}" required
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm @error('email') border-red-500 @enderror">
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor Telepon</label>
                            <input type="tel" name="phone" value="{{ old('phone', $teacher->phone) }}"
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm @error('phone') border-red-500 @enderror">
                            @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: Alamat -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Alamat
                    </h3>
                    <textarea name="address" rows="3"
                              class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-sm @error('address') border-red-500 @enderror">{{ old('address', $teacher->address) }}</textarea>
                    @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- SECTION 3: Akun & Role -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Akun & Role
                    </h3>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Password Baru</label>
                            <input type="text" name="password" value=""
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-sm @error('password') border-red-500 @enderror"
                                   placeholder="Kosongkan jika tidak diubah">
                            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            <p class="text-[10px] text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengubah password</p>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">Status Wakasek</label>
                                <p class="text-xs text-gray-500">Aktifkan untuk memberikan akses/peran Wakasek</p>
                                @if($teacher->classHomeroom()->count() > 0)
                                    <p class="text-xs text-red-500 mt-1 font-medium">Tidak dapat diubah: Guru masih menjabat sebagai Wali Kelas.</p>
                                @endif
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_wakasek" value="1"
                                       class="sr-only peer"
                                       {{ $teacher->is_wakasek ? 'checked' : '' }}
                                       {{ $teacher->classHomeroom()->count() > 0 ? 'disabled' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 {{ $teacher->classHomeroom()->count() > 0 ? 'opacity-50 cursor-not-allowed' : '' }}"></div>
                            </label>
                        </div>
                    </div>
                </div>

                @if($teacher->created_at)
                <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-100 flex flex-col md:flex-row justify-between text-sm text-blue-800">
                    <div><span class="font-semibold">Terdaftar:</span> {{ $teacher->created_at->format('d M Y, H:i') }}</div>
                    <div><span class="font-semibold">Diperbarui:</span> {{ $teacher->updated_at->format('d M Y, H:i') }}</div>
                </div>
                @endif
            </div>
            
            <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.teachers.index') }}" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-100 transition-all">Batal</a>
                <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection