{{-- resources/views/super_admin/admins/create.blade.php --}}
@extends('layouts.super_admin')

@section('title', __('Tambah Admin Sekolah'))
@section('header', __('Tambah Admin Sekolah'))

@push('styles')
<style>
.form-field { position: relative; }
.form-field .field-icon {
    position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
    color: #9ca3af; pointer-events: none; width: 18px; height: 18px;
    transition: color 0.2s;
}
.form-field input:focus ~ .field-icon,
.form-field select:focus ~ .field-icon { color: #7c3aed; }
.form-field input, .form-field select {
    padding-left: 42px !important;
}
.pwd-toggle { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #9ca3af; }
.pwd-toggle:hover { color: #6b7280; }

.strength-bar { height: 4px; border-radius: 2px; background: #e5e7eb; overflow: hidden; transition: all 0.3s; }
.strength-fill { height: 100%; width: 0; border-radius: 2px; transition: width 0.4s ease, background-color 0.4s ease; }

/* Breadcrumb */
.breadcrumb-link { color: #6b7280; font-size: 0.8125rem; font-weight: 600; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #111827; }

/* Step indicator */
.step-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: 50%;
    font-size: 0.75rem; font-weight: 800;
}
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto pb-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 mb-6">
        <a href="{{ route('super-admin.admins.index') }}" class="breadcrumb-link">Admin Sekolah</a>
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-sm font-semibold text-gray-900">Tambah Admin Baru</span>
    </nav>

    {{-- Page title --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 bg-green-600 rounded-2xl flex items-center justify-center shadow-lg shadow-green-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-black text-gray-900 leading-tight">Tambah Admin Sekolah</h1>
                <p class="text-xs text-gray-500 font-medium">Buat akun administrator baru untuk instansi sekolah</p>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-bold text-red-700">Ada kesalahan yang perlu diperbaiki:</p>
                    <ul class="mt-1 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-xs text-red-600">• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('super-admin.admins.store') }}" method="POST" id="adminForm">
        @csrf

        {{-- Card: Informasi Akun --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-4">
            <div class="px-8 py-5 border-b border-gray-50 bg-gray-50/50 flex items-center gap-3">
                <span class="step-badge bg-green-100 text-green-700">1</span>
                <div>
                    <p class="text-sm font-bold text-gray-800">Informasi Akun</p>
                    <p class="text-xs text-gray-400">Nama, email, dan kata sandi admin</p>
                </div>
            </div>
            <div class="p-8 space-y-5">
                {{-- Nama --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                    <div class="form-field">
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="w-full bg-gray-50 border border-transparent rounded-2xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all placeholder-gray-400 @error('name') border-red-300 bg-red-50 @enderror"
                            placeholder="Contoh: Budi Santoso" required>
                        <svg class="field-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Email <span class="text-red-500">*</span></label>
                    <div class="form-field">
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full bg-gray-50 border border-transparent rounded-2xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all placeholder-gray-400 @error('email') border-red-300 bg-red-50 @enderror"
                            placeholder="admin@sekolah.sch.id" required>
                        <svg class="field-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Password <span class="text-red-500">*</span></label>
                    <div class="form-field relative">
                        <input type="password" name="password" id="pwdInput"
                            class="w-full bg-gray-50 border border-transparent rounded-2xl px-4 py-3 pr-12 text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all placeholder-gray-400 @error('password') border-red-300 bg-red-50 @enderror"
                            placeholder="Minimal 8 karakter" required oninput="checkStrength(this.value)">
                        <svg class="field-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <button type="button" class="pwd-toggle" onclick="togglePwd('pwdInput', this)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    <div class="mt-2 space-y-1.5">
                        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                        <p class="text-xs text-gray-400" id="strengthLabel">Belum ada password</p>
                    </div>
                    @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Konfirmasi Password <span class="text-red-500">*</span></label>
                    <div class="form-field relative">
                        <input type="password" name="password_confirmation" id="pwdConfirmInput"
                            class="w-full bg-gray-50 border border-transparent rounded-2xl px-4 py-3 pr-12 text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all placeholder-gray-400"
                            placeholder="Ulangi password" required>
                        <svg class="field-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <button type="button" class="pwd-toggle" onclick="togglePwd('pwdConfirmInput', this)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1" id="matchLabel"></p>
                </div>
            </div>
        </div>

        {{-- Card: Penugasan Instansi --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <div class="px-8 py-5 border-b border-gray-50 bg-gray-50/50 flex items-center gap-3">
                <span class="step-badge bg-green-100 text-green-700">2</span>
                <div>
                    <p class="text-sm font-bold text-gray-800">Penugasan Instansi</p>
                    <p class="text-xs text-gray-400">Pilih sekolah yang akan dikelola admin ini</p>
                </div>
            </div>
            <div class="p-8">
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Instansi Sekolah <span class="text-red-500">*</span></label>
                <div class="form-field">
                    <select name="institution_id" required
                        class="w-full bg-gray-50 border border-transparent rounded-2xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all appearance-none @error('institution_id') border-red-300 bg-red-50 @enderror">
                        <option value="">— Pilih Instansi —</option>
                        @foreach($institutions as $institution)
                            <option value="{{ $institution->id }}" {{ old('institution_id') == $institution->id ? 'selected' : '' }}>
                                {{ $institution->name }}
                            </option>
                        @endforeach
                    </select>
                    <svg class="field-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                @error('institution_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-400 mt-2">Admin hanya bisa mengelola data instansi yang dipilih.</p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('super-admin.admins.index') }}"
                class="inline-flex items-center gap-2 px-5 py-3 text-sm font-bold text-gray-600 bg-white border border-gray-200 rounded-2xl hover:bg-gray-50 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Batal
            </a>
            <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white bg-green-600 rounded-2xl shadow-lg shadow-green-500/25 hover:bg-green-700 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Simpan Admin
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.style.color = isText ? '#9ca3af' : '#6b7280';
}

function checkStrength(val) {
    const fill = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const configs = [
        { w: '0%',   color: '#e5e7eb', text: 'Belum ada password' },
        { w: '25%',  color: '#ef4444', text: 'Lemah' },
        { w: '50%',  color: '#f97316', text: 'Cukup' },
        { w: '75%',  color: '#eab308', text: 'Baik' },
        { w: '100%', color: '#22c55e', text: 'Kuat' },
    ];
    const cfg = val.length === 0 ? configs[0] : configs[score];
    fill.style.width = cfg.w;
    fill.style.backgroundColor = cfg.color;
    label.textContent = cfg.text;
    label.style.color = cfg.color;
}

// Match check
document.getElementById('pwdConfirmInput').addEventListener('input', function() {
    const pwd = document.getElementById('pwdInput').value;
    const lbl = document.getElementById('matchLabel');
    if (!this.value) { lbl.textContent = ''; return; }
    if (this.value === pwd) {
        lbl.textContent = '✓ Password cocok';
        lbl.style.color = '#22c55e';
    } else {
        lbl.textContent = '✗ Password tidak cocok';
        lbl.style.color = '#ef4444';
    }
});
</script>
@endpush
@endsection
