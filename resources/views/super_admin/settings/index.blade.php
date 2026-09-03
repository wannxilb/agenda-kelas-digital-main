{{-- resources/views/super_admin/settings/index.blade.php --}}
@extends('layouts.super_admin')

@section('title', 'Pengaturan Sistem')
@section('header', 'Pengaturan Sistem')

@push('styles')
<style>
/* Custom Toggle Switch - works without Tailwind build */
.toggle-wrap { position: relative; display: inline-block; width: 44px; height: 24px; }
.toggle-wrap input { opacity: 0; width: 0; height: 0; position: absolute; }
.toggle-track {
    position: absolute; inset: 0;
    background-color: #d1d5db;
    border-radius: 9999px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}
.toggle-track::after {
    content: '';
    position: absolute;
    width: 20px; height: 20px;
    top: 2px; left: 2px;
    background: white;
    border-radius: 50%;
    transition: transform 0.2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.toggle-wrap input:checked + .toggle-track {
    background-color: #10b981; /* emerald-500 */
}
.toggle-wrap input:checked + .toggle-track::after {
    transform: translateX(20px);
}
.toggle-wrap input:focus + .toggle-track {
    box-shadow: 0 0 0 3px rgba(16,185,129,0.2);
}
</style>
@endpush

@section('content')

<div class="pb-8" x-data="{ activeTab: '{{ $tab }}' }">

    {{-- Page Header --}}
    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm mb-8">
        <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ __('Pengaturan Sistem') }}</h1>
        <p class="mt-1 text-sm text-gray-500 font-medium">{{ __('Pusat konfigurasi platform Agenda Kelas Digital.') }}</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Sidebar Navigation --}}
        <div class="w-full lg:w-64 shrink-0">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-4 sticky top-28">
                <nav class="space-y-1">
                    @php
                        $tabs = [
                            'general'        => ['label' => __('General'),        'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                            'authentication' => ['label' => __('Authentication'), 'icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'],
                            'institution'    => ['label' => __('Institusi'),      'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                            'features'       => ['label' => __('Fitur'),          'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                            'security'       => ['label' => __('Keamanan'),       'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                            'email'          => ['label' => __('Email'),          'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                            'maintenance'    => ['label' => __('Maintenance'),    'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                            'backup'         => ['label' => __('Backup & Restore'),'icon' => 'M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4'],
                            'audit'          => ['label' => __('Audit Log'),      'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                            'about'          => ['label' => __('Tentang Sistem'), 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ];
                    @endphp

                    @foreach($tabs as $tabKey => $tabInfo)
                        <button
                            @click="activeTab = '{{ $tabKey }}'; window.history.replaceState(null, '', '?tab={{ $tabKey }}')"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200"
                            :class="activeTab === '{{ $tabKey }}' ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tabInfo['icon'] }}"></path>
                                @if($tabKey === 'general')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                @endif
                            </svg>
                            <span>{{ $tabInfo['label'] }}</span>
                            @if($tabKey === 'maintenance' && ($settings['maintenance_enabled'] ?? '0') === '1')
                                <span class="ml-auto w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                            @endif
                        </button>
                    @endforeach
                </nav>
            </div>
        </div>

        {{-- Content Panels --}}
        <div class="flex-1 min-w-0">

            {{-- ═══════════════════════════════════════════ --}}
            {{-- TAB: General --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
                    <h2 class="text-lg font-black text-gray-900 mb-1">{{ __('General Settings') }}</h2>
                    <p class="text-sm text-gray-500 mb-8">{{ __('Informasi dasar tentang aplikasi dan konfigurasi regional.') }}</p>

                    <form action="{{ route('super-admin.settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_tab" value="general">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('Nama Aplikasi') }}</label>
                                <input type="text" name="app_name" value="{{ $settings['app_name'] ?? 'Agenda Kelas Digital' }}" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('Versi Aplikasi') }}</label>
                                <input type="text" name="app_version" value="{{ $settings['app_version'] ?? '1.0.0' }}" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('Nama Developer / Perusahaan') }}</label>
                                <input type="text" name="app_developer" value="{{ $settings['app_developer'] ?? '' }}" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all" placeholder="Contoh: PT Edukasi Digital">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('Copyright Footer') }}</label>
                                <input type="text" name="app_copyright" value="{{ $settings['app_copyright'] ?? '© 2026 Agenda Kelas Digital' }}" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('Timezone') }}</label>
                                <select name="app_timezone" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                                    @foreach(['Asia/Jakarta' => 'WIB (Asia/Jakarta)', 'Asia/Makassar' => 'WITA (Asia/Makassar)', 'Asia/Jayapura' => 'WIT (Asia/Jayapura)'] as $tz => $label)
                                        <option value="{{ $tz }}" {{ ($settings['app_timezone'] ?? 'Asia/Jakarta') === $tz ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('Bahasa Default') }}</label>
                                <select name="app_language" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                                    <option value="id" {{ ($settings['app_language'] ?? 'id') === 'id' ? 'selected' : '' }}>{{ __('Bahasa Indonesia') }}</option>
                                    <option value="en" {{ ($settings['app_language'] ?? 'id') === 'en' ? 'selected' : '' }}>English</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('Format Tanggal') }}</label>
                                <select name="app_date_format" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                                    @foreach(['d/m/Y' => 'DD/MM/YYYY (31/12/2026)', 'Y-m-d' => 'YYYY-MM-DD (2026-12-31)', 'd-m-Y' => 'DD-MM-YYYY (31-12-2026)', 'd F Y' => 'DD Month YYYY (31 Desember 2026)'] as $fmt => $label)
                                        <option value="{{ $fmt }}" {{ ($settings['app_date_format'] ?? 'd/m/Y') === $fmt ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('Format Jam') }}</label>
                                <select name="app_time_format" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                                    <option value="H:i" {{ ($settings['app_time_format'] ?? 'H:i') === 'H:i' ? 'selected' : '' }}>24 Jam (14:30)</option>
                                    <option value="h:i A" {{ ($settings['app_time_format'] ?? 'H:i') === 'h:i A' ? 'selected' : '' }}>12 Jam (02:30 PM)</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end mt-8 pt-6 border-t border-gray-100">
                            <button type="submit" class="px-6 py-3 bg-purple-600 text-white font-bold rounded-2xl shadow-lg shadow-purple-500/20 hover:bg-purple-700 transition-all">{{ __('Simpan Perubahan') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- TAB: Authentication --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div x-show="activeTab === 'authentication'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
                    <h2 class="text-lg font-black text-gray-900 mb-1">Authentication Settings</h2>
                    <p class="text-sm text-gray-500 mb-8">Konfigurasi proses login, registrasi, dan sesi pengguna.</p>

                    <form action="{{ route('super-admin.settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_tab" value="authentication">
                        <div class="space-y-6">
                            {{-- Toggle switches --}}
                            <div class="space-y-4">
                                <p class="text-sm font-bold text-gray-700">Kontrol Akses</p>
                                @php
                                    $authToggles = [
                                        'auth_allow_login'          => ['label' => 'Izinkan Login',                        'default' => '1'],
                                        'auth_login_email'          => ['label' => 'Login menggunakan Email',              'default' => '1'],
                                        'auth_login_username'       => ['label' => 'Login menggunakan Username',           'default' => '0'],
                                        'auth_force_password_change'=> ['label' => 'Wajib Ganti Password Pertama Kali',    'default' => '0'],
                                        'auth_auto_logout'          => ['label' => 'Auto Logout saat Tidak Aktif',         'default' => '1'],
                                    ];
                                @endphp
                                @foreach($authToggles as $key => $toggle)
                                    <label class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors cursor-pointer">
                                        <span class="text-sm font-medium text-gray-700">{{ $toggle['label'] }}</span>
                                        <label class="toggle-wrap">
                                            <input type="hidden" name="{{ $key }}" value="0">
                                            <input type="checkbox" name="{{ $key }}" value="1" {{ ($settings[$key] ?? $toggle['default']) === '1' ? 'checked' : '' }}>
                                            <span class="toggle-track"></span>
                                        </label>
                                    </label>
                                @endforeach
                            </div>

                            <div class="border-t border-gray-100 pt-6">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Session Timeout (menit)</label>
                                <input type="number" name="auth_session_timeout" value="{{ $settings['auth_session_timeout'] ?? '30' }}" min="5" max="1440" class="w-full md:w-48 bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                                <p class="text-xs text-gray-400 mt-1">Pengguna akan otomatis logout setelah tidak aktif selama waktu ini.</p>
                            </div>
                        </div>
                        <div class="flex justify-end mt-8 pt-6 border-t border-gray-100">
                            <button type="submit" class="px-6 py-3 bg-purple-600 text-white font-bold rounded-2xl shadow-lg shadow-purple-500/20 hover:bg-purple-700 transition-all">{{ __('Simpan Perubahan') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- TAB: Institution --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div x-show="activeTab === 'institution'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
                    <h2 class="text-lg font-black text-gray-900 mb-1">Institution Settings</h2>
                    <p class="text-sm text-gray-500 mb-8">Pengaturan default yang berlaku untuk setiap instansi/sekolah baru.</p>

                    <form action="{{ route('super-admin.settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_tab" value="institution">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Jumlah Maksimal Admin per Institusi</label>
                                <input type="number" name="inst_max_admins" value="{{ $settings['inst_max_admins'] ?? '2' }}" min="1" max="10" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Jumlah Maksimal Guru per Institusi</label>
                                <input type="number" name="inst_max_teachers" value="{{ $settings['inst_max_teachers'] ?? '500' }}" min="1" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Jumlah Maksimal Siswa per Institusi</label>
                                <input type="number" name="inst_max_students" value="{{ $settings['inst_max_students'] ?? '5000' }}" min="1" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Jumlah Maksimal Kelas per Institusi</label>
                                <input type="number" name="inst_max_classes" value="{{ $settings['inst_max_classes'] ?? '100' }}" min="1" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Default Status Institusi Baru</label>
                                <select name="inst_default_status" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                                    <option value="active" {{ ($settings['inst_default_status'] ?? 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="suspended" {{ ($settings['inst_default_status'] ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="maintenance" {{ ($settings['inst_default_status'] ?? '') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Masa Aktif Default (hari)</label>
                                <input type="number" name="inst_default_expiry_days" value="{{ $settings['inst_default_expiry_days'] ?? '365' }}" min="30" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                                <p class="text-xs text-gray-400 mt-1">0 = tidak ada batas waktu.</p>
                            </div>
                        </div>
                        <div class="flex justify-end mt-8 pt-6 border-t border-gray-100">
                            <button type="submit" class="px-6 py-3 bg-purple-600 text-white font-bold rounded-2xl shadow-lg shadow-purple-500/20 hover:bg-purple-700 transition-all">{{ __('Simpan Perubahan') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- TAB: Feature Management --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div x-show="activeTab === 'features'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
                    <h2 class="text-lg font-black text-gray-900 mb-1">Feature Management</h2>
                    <p class="text-sm text-gray-500 mb-8">Aktifkan atau nonaktifkan modul fitur platform secara global.</p>

                    <form action="{{ route('super-admin.settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_tab" value="features">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                                $features = \App\Services\FeatureService::all();
                            @endphp
                            @foreach($features as $featureKey => $feature)
                                @php $key = $feature['key']; @endphp
                                <label class="flex items-start gap-4 p-5 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors cursor-pointer group">
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-gray-800">{{ $feature['label'] }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $feature['desc'] }}</p>
                                    </div>
                                    <label class="toggle-wrap shrink-0 mt-0.5">
                                        <input type="hidden" name="{{ $key }}" value="0">
                                        <input type="checkbox" name="{{ $key }}" value="1" {{ ($settings[$key] ?? ($feature['default'] ? '1' : '0')) === '1' ? 'checked' : '' }}>
                                        <span class="toggle-track"></span>
                                    </label>
                                </label>
                            @endforeach
                        </div>
                        <div class="flex justify-end mt-8 pt-6 border-t border-gray-100">
                            <button type="submit" class="px-6 py-3 bg-purple-600 text-white font-bold rounded-2xl shadow-lg shadow-purple-500/20 hover:bg-purple-700 transition-all">{{ __('Simpan Perubahan') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- TAB: Security --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div x-show="activeTab === 'security'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
                    <h2 class="text-lg font-black text-gray-900 mb-1">Security Settings</h2>
                    <p class="text-sm text-gray-500 mb-8">Konfigurasi keamanan akun dan perlindungan sistem.</p>

                    <form action="{{ route('super-admin.settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_tab" value="security">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Minimal Panjang Password</label>
                                <div class="flex items-center gap-3">
                                    <input type="number" name="sec_min_password" value="{{ $settings['sec_min_password'] ?? '8' }}" min="6" max="32" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                                    <span class="text-sm text-gray-400 whitespace-nowrap">karakter</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Maksimal Percobaan Login Gagal</label>
                                <div class="flex items-center gap-3">
                                    <input type="number" name="sec_max_login_attempts" value="{{ $settings['sec_max_login_attempts'] ?? '5' }}" min="3" max="20" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                                    <span class="text-sm text-gray-400 whitespace-nowrap">kali</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Durasi Lock Account</label>
                                <div class="flex items-center gap-3">
                                    <input type="number" name="sec_lockout_duration" value="{{ $settings['sec_lockout_duration'] ?? '15' }}" min="1" max="1440" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                                    <span class="text-sm text-gray-400 whitespace-nowrap">menit</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Masa Berlaku Password</label>
                                <div class="flex items-center gap-3">
                                    <input type="number" name="sec_password_expiry_days" value="{{ $settings['sec_password_expiry_days'] ?? '0' }}" min="0" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                                    <span class="text-sm text-gray-400 whitespace-nowrap">hari</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">0 = tidak ada masa berlaku.</p>
                            </div>
                        </div>

                        <div class="flex justify-end mt-8 pt-6 border-t border-gray-100">
                            <button type="submit" class="px-6 py-3 bg-purple-600 text-white font-bold rounded-2xl shadow-lg shadow-purple-500/20 hover:bg-purple-700 transition-all">{{ __('Simpan Perubahan') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- TAB: Email Configuration --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div x-show="activeTab === 'email'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
                    <h2 class="text-lg font-black text-gray-900 mb-1">Email Configuration</h2>
                    <p class="text-sm text-gray-500 mb-8">Konfigurasi SMTP untuk pengiriman email reset password.</p>

                    <form action="{{ route('super-admin.settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_tab" value="email">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">SMTP Host</label>
                                <input type="text" name="mail_host" value="{{ $settings['mail_host'] ?? '' }}" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all" placeholder="smtp.gmail.com">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">SMTP Port</label>
                                <input type="number" name="mail_port" value="{{ $settings['mail_port'] ?? '587' }}" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all" placeholder="587">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">SMTP Username</label>
                                <input type="text" name="mail_username" value="{{ $settings['mail_username'] ?? '' }}" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all" placeholder="email@domain.com">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">SMTP Password</label>
                                <input type="password" name="mail_password" value="{{ $settings['mail_password'] ?? '' }}" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all" placeholder="••••••••">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Encryption</label>
                                <select name="mail_encryption" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                                    <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="" {{ ($settings['mail_encryption'] ?? 'tls') === '' ? 'selected' : '' }}>Tanpa Enkripsi</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Sender Name</label>
                                <input type="text" name="mail_from_name" value="{{ $settings['mail_from_name'] ?? 'Agenda Kelas Digital' }}" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Sender Email</label>
                                <input type="email" name="mail_from_address" value="{{ $settings['mail_from_address'] ?? '' }}" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all" placeholder="noreply@agendakelas.com">
                            </div>
                        </div>
                        <div class="flex justify-end mt-8 pt-6 border-t border-gray-100">
                            <button type="submit" class="px-6 py-3 bg-purple-600 text-white font-bold rounded-2xl shadow-lg shadow-purple-500/20 hover:bg-purple-700 transition-all">{{ __('Simpan Perubahan') }}</button>
                        </div>
                    </form>

                    {{-- Test Email --}}
                    <div class="border-t border-gray-100 mt-8 pt-8">
                        <h3 class="text-sm font-bold text-gray-700 mb-4">Test Email</h3>
                        <form action="{{ route('super-admin.settings.testEmail') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                            @csrf
                            <input type="email" name="test_email_address" required placeholder="Masukkan email tujuan test..." class="flex-1 bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                            <button type="submit" class="px-6 py-3 bg-emerald-600 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-700 transition-all whitespace-nowrap">Kirim Test Email</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- ═══════════════════════════════════════════ --}}
            {{-- ═══════════════════════════════════════════ --}}
            {{-- TAB: Maintenance Mode --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div x-show="activeTab === 'maintenance'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
                    <h2 class="text-lg font-black text-gray-900 mb-1">Maintenance Mode</h2>
                    <p class="text-sm text-gray-500 mb-8">Aktifkan mode maintenance untuk menutup akses sementara bagi seluruh pengguna non-Super Admin.</p>

                    @if(($settings['maintenance_enabled'] ?? '0') === '1')
                        <div class="mb-8 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg">
                            <div class="flex">
                                <svg class="h-5 w-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm font-bold text-amber-700">Mode Maintenance sedang AKTIF</p>
                                    <p class="text-xs text-amber-600 mt-1">Seluruh pengguna selain Super Admin tidak dapat mengakses sistem.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('super-admin.settings.maintenance') }}" method="POST" x-data="{ enabled: {{ ($settings['maintenance_enabled'] ?? '0') === '1' ? 'true' : 'false' }} }">
                        @csrf
                        <div class="space-y-6">
                            <div class="flex items-center justify-between p-5 rounded-2xl transition-colors" :class="enabled ? 'bg-amber-50 border-2 border-amber-300' : 'bg-gray-50 border-2 border-transparent'">
                                <div>
                                    <span class="text-base font-bold" :class="enabled ? 'text-amber-700' : 'text-gray-700'">Mode Maintenance</span>
                                    <p class="text-xs mt-0.5" :class="enabled ? 'text-amber-500' : 'text-gray-400'">
                                        <span x-text="enabled ? 'Sistem sedang dalam mode maintenance' : 'Sistem beroperasi normal'"></span>
                                    </p>
                                </div>
                                
                                {{-- Tombol Toggle yang lebih bersih --}}
                                <button type="button" @click="enabled = !enabled" class="relative inline-flex h-7 w-14 items-center rounded-full transition-colors focus:outline-none" :class="enabled ? 'bg-amber-500' : 'bg-gray-300'">
                                    <span class="inline-block h-5 w-5 transform rounded-full bg-white transition-transform" :class="enabled ? 'translate-x-8' : 'translate-x-1'"></span>
                                </button>
                                <input type="hidden" name="maintenance_enabled" :value="enabled ? '1' : '0'">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Pesan Maintenance</label>
                                <textarea name="maintenance_message" rows="4" class="w-full bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all" placeholder="Contoh: Sistem sedang maintenance. Silakan kembali pukul 20.00 WIB.">{{ $settings['maintenance_message'] ?? 'Sistem sedang dalam pemeliharaan. Mohon coba lagi nanti.' }}</textarea>
                            </div>
                        </div>
                        <div class="flex justify-end mt-8 pt-6 border-t border-gray-100">
                            <button type="submit" class="px-6 py-3 font-bold rounded-2xl shadow-lg transition-all" :class="enabled ? 'bg-amber-600 text-white shadow-amber-500/20 hover:bg-amber-700' : 'bg-purple-600 text-white shadow-purple-500/20 hover:bg-purple-700'">
                                <span x-text="enabled ? 'Aktifkan Maintenance' : 'Simpan Perubahan'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- TAB: Backup & Restore --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div x-show="activeTab === 'backup'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
                    <h2 class="text-lg font-black text-gray-900 mb-1">Backup & Restore</h2>
                    <p class="text-sm text-gray-500 mb-8">Amankan data sistem dengan melakukan pencadangan secara berkala.</p>

                    <div class="space-y-6">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-6 bg-purple-50 rounded-2xl border border-purple-100">
                            <div class="mb-4 sm:mb-0">
                                <h3 class="text-base font-bold text-purple-900">Backup Database</h3>
                                <p class="text-sm text-purple-700 mt-1">Unduh salinan lengkap database sistem saat ini (.sql).</p>
                            </div>
                            <form action="{{ route('super-admin.settings.backup.database') }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 text-white font-bold rounded-xl shadow-md shadow-purple-500/20 hover:bg-purple-700 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download SQL
                                </button>
                            </form>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-6 bg-indigo-50 rounded-2xl border border-indigo-100">
                            <div class="mb-4 sm:mb-0">
                                <h3 class="text-base font-bold text-indigo-900">Backup File Sistem</h3>
                                <p class="text-sm text-indigo-700 mt-1">Unduh seluruh file terunggah (Storage) dalam format .zip.</p>
                            </div>
                            <form action="{{ route('super-admin.settings.backup.files') }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white font-bold rounded-xl shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download ZIP
                                </button>
                            </form>
                        </div>

                        <div class="pt-8 mt-4 border-t border-gray-100">
                            <h3 class="text-sm font-bold text-gray-700 mb-4">Restore Database</h3>
                            <form action="{{ route('super-admin.settings.backup.restore') }}" method="POST" enctype="multipart/form-data" class="bg-gray-50 p-6 rounded-2xl border border-gray-200 border-dashed" onsubmit="return confirm('Peringatan: Proses ini akan menimpa seluruh data saat ini dengan data dari file backup. Apakah Anda yakin ingin melanjutkan?')">
                                @csrf
                                <div class="flex flex-col sm:flex-row gap-4 items-end">
                                    <div class="flex-1 w-full">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File Backup (.sql)</label>
                                        <input type="file" name="backup_file" accept=".sql" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    </div>
                                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-red-600 text-white font-bold rounded-xl shadow-md shadow-red-500/20 hover:bg-red-700 transition-all whitespace-nowrap">
                                        Mulai Restore
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- TAB: Audit Log Settings --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div x-show="activeTab === 'audit'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
                    <h2 class="text-lg font-black text-gray-900 mb-1">Audit Log Settings</h2>
                    <p class="text-sm text-gray-500 mb-8">Tentukan apa saja yang dicatat oleh sistem audit dan berapa lama disimpan.</p>

                    <form action="{{ route('super-admin.settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_tab" value="audit">
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Retensi Log (hari)</label>
                                <div class="flex items-center gap-3">
                                    <input type="number" name="audit_retention_days" value="{{ $settings['audit_retention_days'] ?? '365' }}" min="30" max="3650" class="w-full md:w-48 bg-gray-50 border-transparent rounded-2xl px-4 py-3 focus:bg-white focus:ring-2 focus:ring-purple-500 transition-all">
                                    <span class="text-sm text-gray-400">hari</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Log yang lebih lama dari ini akan dihapus otomatis.</p>
                            </div>
                            <div class="border-t border-gray-100 pt-6 space-y-4">
                                <p class="text-sm font-bold text-gray-700">Jenis Aktivitas yang Dicatat</p>
                                @php
                                    $auditOptions = [
                                        'audit_log_login' => ['label' => 'Log Login / Logout',    'desc' => 'Catat setiap aktivitas login dan logout.',           'default' => '1'],
                                        'audit_log_crud'  => ['label' => 'Log CRUD',              'desc' => 'Catat setiap perubahan data (create/update/delete).', 'default' => '1'],
                                        'audit_log_error' => ['label' => 'Log Error',             'desc' => 'Catat error aplikasi untuk debugging.',              'default' => '1'],
                                    ];
                                @endphp
                                @foreach($auditOptions as $key => $opt)
                                    <label class="flex items-start gap-4 p-5 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors cursor-pointer">
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-gray-800">{{ $opt['label'] }}</p>
                                            <p class="text-xs text-gray-400 mt-0.5">{{ $opt['desc'] }}</p>
                                        </div>
                                        <label class="toggle-wrap shrink-0 mt-0.5">
                                            <input type="hidden" name="{{ $key }}" value="0">
                                            <input type="checkbox" name="{{ $key }}" value="1" {{ ($settings[$key] ?? $opt['default']) === '1' ? 'checked' : '' }}>
                                            <span class="toggle-track"></span>
                                        </label>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex justify-end mt-8 pt-6 border-t border-gray-100">
                            <button type="submit" class="px-6 py-3 bg-purple-600 text-white font-bold rounded-2xl shadow-lg shadow-purple-500/20 hover:bg-purple-700 transition-all">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- TAB: About System --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div x-show="activeTab === 'about'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
                    <h2 class="text-lg font-black text-gray-900 mb-1">Tentang Sistem</h2>
                    <p class="text-sm text-gray-500 mb-8">Informasi teknis tentang lingkungan server dan aplikasi.</p>

                    <div class="space-y-4">
                        @php
                            $infoItems = [
                                ['label' => 'Versi Aplikasi',   'value' => $settings['app_version'] ?? '1.0.0',           'color' => 'purple'],
                                ['label' => 'Laravel Framework', 'value' => 'v' . $systemInfo['laravel_version'],          'color' => 'red'],
                                ['label' => 'PHP Version',       'value' => 'v' . $systemInfo['php_version'],              'color' => 'indigo'],
                                ['label' => 'Database Driver',   'value' => ucfirst($systemInfo['database']),              'color' => 'blue'],
                                ['label' => 'Environment',       'value' => ucfirst($systemInfo['environment']),           'color' => 'emerald'],
                                ['label' => 'Server OS',         'value' => $systemInfo['server_os'],                      'color' => 'gray'],
                                ['label' => 'Storage Path',      'value' => $systemInfo['storage_path'],                   'color' => 'gray'],
                                ['label' => 'Developer',         'value' => $settings['app_developer'] ?? '-',             'color' => 'amber'],
                            ];
                        @endphp
                        @foreach($infoItems as $item)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
                                <span class="text-sm font-bold text-gray-600">{{ $item['label'] }}</span>
                                <span class="text-sm font-mono font-semibold text-{{ $item['color'] }}-600 bg-{{ $item['color'] }}-50 px-3 py-1 rounded-lg">{{ $item['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
