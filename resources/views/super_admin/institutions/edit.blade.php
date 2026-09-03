{{-- resources/views/super_admin/institutions/edit.blade.php --}}
@extends('layouts.super_admin')

@section('title', __('Edit Instansi'))
@section('header', __('Edit Instansi'))

@push('styles')
<style>
.form-field { position: relative; }
.field-icon {
    position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
    color: #9ca3af; pointer-events: none; width: 18px; height: 18px;
    transition: color 0.2s;
}
.textarea-icon { top: 16px; transform: none; }
.form-field input, .form-field select { padding-left: 42px !important; }
.form-field textarea { padding-left: 42px !important; }

.status-card {
    border: 2px solid transparent;
    border-radius: 16px;
    padding: 14px 16px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex; align-items: center; gap: 12px;
}
.status-card:hover { background: #f9fafb; }
.status-card input[type="radio"] { display: none; }
.status-card.selected-active   { border-color: #22c55e; background: #f0fdf4; }
.status-card.selected-suspended{ border-color: #ef4444; background: #fef2f2; }
.status-card.selected-maintenance{ border-color: #f97316; background: #fff7ed; }
.status-card.selected-expired  { border-color: #6b7280; background: #f9fafb; }

.dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.dot-active     { background: #22c55e; }
.dot-suspended  { background: #ef4444; }
.dot-maintenance{ background: #f97316; }
.dot-expired    { background: #6b7280; }

.breadcrumb-link { color: #6b7280; font-size: 0.8125rem; font-weight: 600; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #111827; }
.step-badge { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; font-size: 0.75rem; font-weight: 800; }
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto pb-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 mb-6">
        <a href="{{ route('super-admin.institutions.index') }}" class="breadcrumb-link">Manajemen Instansi</a>
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-sm font-semibold text-gray-900">Edit Instansi</span>
    </nav>

    {{-- Page title --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 bg-purple-600 rounded-2xl flex items-center justify-center shadow-lg shadow-purple-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-black text-gray-900 leading-tight">Edit Instansi</h1>
                <p class="text-xs text-gray-500 font-medium">Perbarui profil dan status instansi sekolah</p>
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

    <form action="{{ route('super-admin.institutions.update', $institution->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Card: Informasi Instansi --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-4">
            <div class="px-8 py-5 border-b border-gray-50 bg-gray-50/50 flex items-center gap-3">
                <span class="step-badge bg-purple-100 text-purple-700">1</span>
                <div>
                    <p class="text-sm font-bold text-gray-800">Informasi Instansi</p>
                    <p class="text-xs text-gray-400">Data dasar sekolah/instansi</p>
                </div>
            </div>
            <div class="p-8 space-y-5">

                {{-- Nama --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Nama Instansi <span class="text-red-500">*</span></label>
                    <div class="form-field">
                        <input type="text" name="name" value="{{ old('name', $institution->name) }}"
                            class="w-full bg-gray-50 border border-transparent rounded-2xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all placeholder-gray-400 @error('name') border-red-300 bg-red-50 @enderror"
                            placeholder="Contoh: SMA Negeri 1 Surabaya" required>
                        <svg class="field-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Email Resmi <span class="text-gray-400 font-normal normal-case">(opsional)</span></label>
                    <div class="form-field">
                        <input type="email" name="email" value="{{ old('email', $institution->email) }}"
                            class="w-full bg-gray-50 border border-transparent rounded-2xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all placeholder-gray-400 @error('email') border-red-300 bg-red-50 @enderror"
                            placeholder="info@sekolah.sch.id">
                        <svg class="field-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Alamat --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Alamat <span class="text-gray-400 font-normal normal-case">(opsional)</span></label>
                    <div class="form-field relative">
                        <textarea name="address" rows="3"
                            class="w-full bg-gray-50 border border-transparent rounded-2xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all placeholder-gray-400 resize-none @error('address') border-red-300 bg-red-50 @enderror"
                            placeholder="Jl. Raya No. 1, Kota, Provinsi">{{ old('address', $institution->address) }}</textarea>
                        <svg class="field-icon textarea-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    @error('address') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Card: Status Instansi --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <div class="px-8 py-5 border-b border-gray-50 bg-gray-50/50 flex items-center gap-3">
                <span class="step-badge bg-purple-100 text-purple-700">2</span>
                <div>
                    <p class="text-sm font-bold text-gray-800">Status Instansi</p>
                    <p class="text-xs text-gray-400">Atur kondisi akses instansi saat ini</p>
                </div>
            </div>
            <div class="p-8">
                <input type="hidden" name="status" id="statusInput" value="{{ old('status', $institution->status) }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="statusCards">

                    <div class="status-card {{ old('status', $institution->status) === 'active' ? 'selected-active' : '' }}" onclick="selectStatus('active', this)">
                        <div class="dot dot-active"></div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Aktif</p>
                            <p class="text-xs text-gray-500">Instansi dapat diakses normal</p>
                        </div>
                    </div>

                    <div class="status-card {{ old('status', $institution->status) === 'suspended' ? 'selected-suspended' : '' }}" onclick="selectStatus('suspended', this)">
                        <div class="dot dot-suspended"></div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Suspended</p>
                            <p class="text-xs text-gray-500">Akses diblokir sementara</p>
                        </div>
                    </div>

                    <div class="status-card {{ old('status', $institution->status) === 'maintenance' ? 'selected-maintenance' : '' }}" onclick="selectStatus('maintenance', this)">
                        <div class="dot dot-maintenance"></div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Maintenance</p>
                            <p class="text-xs text-gray-500">Sedang dalam pemeliharaan</p>
                        </div>
                    </div>

                    <div class="status-card {{ old('status', $institution->status) === 'expired' ? 'selected-expired' : '' }}" onclick="selectStatus('expired', this)">
                        <div class="dot dot-expired"></div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Expired</p>
                            <p class="text-xs text-gray-500">Masa aktif telah berakhir</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('super-admin.institutions.index') }}"
                class="inline-flex items-center gap-2 px-5 py-3 text-sm font-bold text-gray-600 bg-white border border-gray-200 rounded-2xl hover:bg-gray-50 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Batal
            </a>
            <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white bg-purple-600 rounded-2xl shadow-lg shadow-purple-500/25 hover:bg-purple-700 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
const statusClasses = {
    active:      'selected-active',
    suspended:   'selected-suspended',
    maintenance: 'selected-maintenance',
    expired:     'selected-expired',
};

function selectStatus(value, el) {
    // Remove all selected classes
    document.querySelectorAll('#statusCards .status-card').forEach(card => {
        Object.values(statusClasses).forEach(cls => card.classList.remove(cls));
    });
    // Add to clicked
    el.classList.add(statusClasses[value]);
    // Update hidden input
    document.getElementById('statusInput').value = value;
}
</script>
@endpush
@endsection
