@extends('layouts.siswa')

@section('title', 'Profil Saya')
@section('header', 'Profil Saya')

@section('content')
<div class="space-y-4 sm:space-y-6 pb-24 sm:pb-8 max-w-2xl mx-auto">
    {{-- Profile Header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="h-24 bg-linear-to-r from-blue-600 to-indigo-700 relative">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMS41IiBmaWxsPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMSkiLz48L3N2Zz4=')] opacity-50"></div>
        </div>
        <div class="px-5 sm:px-8 pb-6 -mt-10 relative">
            <div class="flex items-end justify-between mb-5">
                <div class="w-20 h-20 bg-white p-1 rounded-2xl shadow-lg">
                    <div class="w-full h-full bg-linear-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white text-2xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                </div>
                <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-blue-100">
                    Siswa
                </span>
            </div>
            <h2 class="text-lg font-black text-gray-900">{{ $user->name }}</h2>
            <p class="text-xs text-gray-400 font-medium mt-0.5">{{ $user->email }}</p>
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center mb-2.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Kelas</p>
            <p class="text-sm font-black text-gray-900 mt-0.5">{{ $user->class->name ?? '-' }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <div class="w-8 h-8 bg-indigo-50 rounded-xl flex items-center justify-center mb-2.5">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
            </div>
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">NIS</p>
            <p class="text-sm font-black text-gray-900 mt-0.5">{{ $user->nis ?? '-' }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <div class="w-8 h-8 bg-purple-50 rounded-xl flex items-center justify-center mb-2.5">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
            </div>
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">NISN</p>
            <p class="text-sm font-black text-gray-900 mt-0.5">{{ $user->nisn ?? '-' }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <div class="w-8 h-8 bg-emerald-50 rounded-xl flex items-center justify-center mb-2.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
            </div>
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Telepon</p>
            <p class="text-sm font-black text-gray-900 mt-0.5">{{ $user->phone ?? '-' }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <div class="w-8 h-8 bg-amber-50 rounded-xl flex items-center justify-center mb-2.5">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Alamat</p>
            <p class="text-sm font-black text-gray-900 mt-0.5 truncate">{{ $user->address ?? '-' }}</p>
        </div>
    </div>

    {{-- Edit Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-3">
            <div class="w-8 h-8 bg-gray-100 rounded-xl flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
            <h3 class="text-sm font-bold text-gray-800">Edit Profil</h3>
        </div>
        <form action="{{ route('siswa.profile.update') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold @error('name') ring-2 ring-red-500 @enderror">
                @error('name') <p class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold @error('email') ring-2 ring-red-500 @enderror">
                @error('email') <p class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold">
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Alamat</label>
                <input type="text" name="address" value="{{ old('address', $user->address) }}"
                       class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold">
            </div>

            {{-- Password Section --}}
            <div x-data="{ open: false }" class="mt-2">
                <button type="button" @click="open = !open" class="w-full flex items-center justify-between py-3 border-t border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-amber-50 rounded-xl flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-700">Ganti Password</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-collapse x-cloak class="space-y-3 pb-1">
                    <p class="text-[10px] text-gray-400">Kosongkan jika tidak ingin mengubah password.</p>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Password Baru</label>
                        <input type="password" name="password"
                               class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold @error('password') ring-2 ring-red-500 @enderror">
                        @error('password') <p class="mt-1 text-[10px] font-bold text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Konfirmasi</label>
                        <input type="password" name="password_confirmation"
                               class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold">
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-gray-900 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-black transition-all active:scale-[0.98]">
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>
@endsection
