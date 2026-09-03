{{-- resources/views/admin/profile.blade.php --}}
@extends(Auth::user()->hasRole('super_admin') ? 'layouts.super_admin' : 'layouts.admin')

@section('title', 'Profil Saya')
@section('header', 'Profil Saya')

@section('content')
<div class="max-w-4xl mx-auto pb-12">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="h-32 bg-linear-to-r from-blue-600 to-indigo-700"></div>
        <div class="px-8 pb-8">
            <div class="relative flex justify-between items-end -mt-12 mb-8">
                <div class="w-24 h-24 bg-white p-1 rounded-2xl shadow-lg">
                    <div class="w-full h-full bg-linear-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white text-3xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                </div>
            </div>

            <form action="{{ route(Auth::user()->hasRole('super_admin') ? 'super-admin.profile.update' : 'admin.profile.update') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">{{ __('Nama Lengkap') }}</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all text-sm font-bold text-gray-900 @error('name') ring-2 ring-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-[10px] font-bold text-red-500 uppercase tracking-tight ml-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">{{ __('Alamat Email') }}</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all text-sm font-bold text-gray-900 @error('email') ring-2 ring-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-[10px] font-bold text-red-500 uppercase tracking-tight ml-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">{{ __('Nomor Telepon') }}</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all text-sm font-bold text-gray-900 @error('phone') ring-2 ring-red-500 @enderror">
                        @error('phone')
                            <p class="mt-1 text-[10px] font-bold text-red-500 uppercase tracking-tight ml-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">{{ __('Alamat') }}</label>
                        <input type="text" name="address" value="{{ old('address', $user->address) }}"
                               class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all text-sm font-bold text-gray-900 @error('address') ring-2 ring-red-500 @enderror">
                        @error('address')
                            <p class="mt-1 text-[10px] font-bold text-red-500 uppercase tracking-tight ml-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <hr class="border-gray-50 my-10">

                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600 shadow-sm border border-amber-100/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900">{{ __('Ganti Password') }}</h3>
                        <p class="text-xs text-gray-500 font-medium">{{ __('Kosongkan jika tidak ingin mengubah password.') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">{{ __('Password Baru') }}</label>
                        <input type="password" name="password" 
                               class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all text-sm font-bold text-gray-900 @error('password') ring-2 ring-red-500 @enderror">
                        @error('password')
                            <p class="mt-1 text-[10px] font-bold text-red-500 uppercase tracking-tight ml-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">{{ __('Konfirmasi Password') }}</label>
                        <input type="password" name="password_confirmation" 
                               class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all text-sm font-bold text-gray-900">
                    </div>
                </div>

                <div class="flex justify-end pt-10">
                    <button type="submit" class="px-10 py-4 bg-linear-to-r from-blue-600 to-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-blue-500/20 hover:scale-[1.02] transition-all duration-300">
                        {{ __('Simpan Perubahan') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
