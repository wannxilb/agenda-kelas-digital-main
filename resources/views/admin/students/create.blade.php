{{-- resources/views/admin/students/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Tambah Siswa')
@section('header', 'Tambah Siswa Baru')

@section('content')
@php
    $fieldBorder = fn (string $field): string => $errors->has($field) ? 'border-red-500' : 'border-gray-200';
@endphp
<div class="max-w-3xl mx-auto pb-12">
    <!-- Breadcrumb & Header -->
    <div class="mb-8">
        <nav class="flex text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.students.index') }}" class="hover:text-indigo-600 transition-colors">Siswa</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">Tambah Siswa</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">Tambah Siswa Baru</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.students.store') }}" method="POST">
            @csrf
            
            <div class="p-8 space-y-8">
                <!-- SECTION 1: Informasi Pribadi -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Informasi Pribadi
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama Lengkap -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('name') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                   placeholder="Contoh: Ahmad Wijaya">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Gender -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <div class="flex space-x-6 mt-3">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="gender" value="L" {{ old('gender') == 'L' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500" required>
                                    <span class="ml-2 text-sm text-gray-700 font-medium">Laki-laki</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="gender" value="P" {{ old('gender') == 'P' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500" required>
                                    <span class="ml-2 text-sm text-gray-700 font-medium">Perempuan</span>
                                </label>
                            </div>
                            @error('gender') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Tempat Lahir -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}"
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('tempat_lahir') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                   placeholder="Contoh: Bandung">
                            @error('tempat_lahir') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Tanggal Lahir -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('tanggal_lahir') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm">
                            @error('tanggal_lahir') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- No Telepon -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">No Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('phone') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                   placeholder="Contoh: 081234567891">
                            @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">No WhatsApp Orang Tua</label>
                            <input type="text" name="parent_phone" value="{{ old('parent_phone') }}"
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('parent_phone') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                   placeholder="Contoh: 081234567891">
                            @error('parent_phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email (Opsional)</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('email') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                   placeholder="Contoh: siswa@school.com">
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            <p class="text-[10px] text-gray-500 mt-1">Jika dikosongkan, email akan di-generate otomatis: <strong>[NIS]@agenda.local</strong></p>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: Informasi Akademik & Akun -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        Informasi Akademik & Akun
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- NIS -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">NIS (Nomor Induk Siswa) <span class="text-red-500">*</span></label>
                            <input type="text" name="nis" value="{{ old('nis') }}" required
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('nis') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                   placeholder="Contoh: 2024001">
                            @error('nis') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            <p class="text-[10px] text-gray-500 mt-1">NIS harus unik. Digunakan sebagai username login.</p>
                        </div>

                        <!-- NISN -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">NISN</label>
                            <input type="text" name="nisn" value="{{ old('nisn') }}"
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('nisn') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                   placeholder="Contoh: 0098765432">
                            @error('nisn') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Kelas -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kelas <span class="text-red-500">*</span></label>
                            <select name="class_id" id="class_id" required
                                    class="w-full text-sm @error('class_id') border-red-500 @enderror">
                                <option value="">Pilih Kelas</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} ({{ $class->grade_level }})
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password Login <span class="text-red-500">*</span></label>
                        <input type="text" name="password" value="{{ old('password', 'password') }}" required
                               class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('password') }} rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-sm">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-[10px] text-gray-500 mt-1">Password default: <strong>password</strong></p>
                    </div>
                </div>

                <!-- SECTION 3: Alamat Lengkap -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Alamat Lengkap
                    </h3>
                    <div class="space-y-6">
                        <!-- Alamat Jalan -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Jalan</label>
                            <textarea name="address" rows="2"
                                      class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('address') }} rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-sm"
                                      placeholder="Nama jalan, nomor rumah, RT/RW...">{{ old('address') }}</textarea>
                            @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- RT, RW, Kelurahan, Kecamatan -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <!-- RT -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">RT</label>
                                <input type="text" name="rt" value="{{ old('rt') }}"
                                       class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('rt') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                       placeholder="Contoh: 03">
                                @error('rt') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- RW -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">RW</label>
                                <input type="text" name="rw" value="{{ old('rw') }}"
                                       class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('rw') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                       placeholder="Contoh: 05">
                                @error('rw') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Kelurahan -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Kelurahan</label>
                                <input type="text" name="kelurahan" value="{{ old('kelurahan') }}"
                                       class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('kelurahan') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                       placeholder="Contoh: Babakan Ciamis">
                                @error('kelurahan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Kecamatan -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Kecamatan</label>
                                <input type="text" name="kecamatan" value="{{ old('kecamatan') }}"
                                       class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('kecamatan') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                       placeholder="Contoh: Sumur Bandung">
                                @error('kecamatan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.students.index') }}" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-100 transition-all">Batal</a>
                <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all shadow-sm">Simpan Siswa</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#class_id', {
            create: false,
            placeholder: 'Cari Kelas...',
            sortField: { field: 'text', direction: 'asc' },
            maxOptions: null,
        });
    });
</script>
@endpush
@endsection
