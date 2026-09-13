@extends('layouts.admin')

@section('title', 'Pengaturan Absensi Harian')
@section('header', 'Pengaturan Absensi Harian')

@section('content')
<div class="max-w-4xl space-y-6">
    <div>
        <h1 class="text-xl font-black text-gray-900">Absensi Masuk/Pulang Siswa</h1>
        <p class="mt-1 text-sm text-gray-500">Atur jam sekolah dan aturan foto untuk absensi harian siswa.</p>
    </div>

    {{-- Kesehatan notifikasi ketidakhadiran (alpha) --}}
    <section class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-gray-100 p-5 sm:p-6 flex items-start justify-between gap-3">
            <div>
                <h2 class="text-sm font-black text-gray-900">Status Notifikasi Ketidakhadiran (Alpha)</h2>
                <p class="mt-1 text-xs text-gray-500">Cek kesiapan pengiriman WhatsApp ke orang tua untuk siswa tidak hadir.</p>
            </div>
            <span class="shrink-0 rounded-xl px-3 py-1.5 text-[10px] font-black uppercase tracking-wider
                {{ $health['all_pass'] ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                {{ $health['all_pass'] ? 'Siap' : 'Perlu Perhatian' }}
            </span>
        </div>

        <div class="grid grid-cols-1 gap-3 p-5 sm:p-6 sm:grid-cols-2">
            @foreach($health['checks'] as $check)
                <div class="flex items-start gap-3 rounded-2xl border border-gray-100 bg-gray-50/60 p-3.5">
                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full
                        {{ $check['ok'] ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                        @if($check['ok'])
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        @else
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                        @endif
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-black text-gray-900">{{ $check['label'] }}</p>
                        <p class="mt-0.5 text-[11px] leading-relaxed {{ $check['ok'] ? 'text-gray-500' : 'text-rose-600' }}">{{ $check['message'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="border-t border-gray-100 px-5 sm:px-6 py-4 text-[11px] text-gray-500">
            @php
                $deadlineNote = $health['deadline_passed']
                    ? "Batas verifikasi masukan ({$health['deadline']}) sudah lewat — siswa yang belum tercatat hadir akan dianggap alpha dan dinotifikasi."
                    : "Batas verifikasi masukan ({$health['deadline']}) belum lewat. Notifikasi alpha baru dikirim otomatis setelah jam ini.";
            @endphp
            {{ $deadlineNote }}
            @if($health['today_absent_logs'] > 0)
                <span class="text-emerald-600"> {{ $health['today_absent_logs'] }} notif alpha terekam hari ini.</span>
            @endif
        </div>
    </section>

    <form method="POST" action="{{ route('admin.daily-attendance.settings.update') }}" class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden" x-data="{ mode: @js(old('whatsapp_mode', $setting->whatsapp_mode)) }">
        @csrf
        @method('PUT')

        <div class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Mulai Masuk</label>
                <input type="time" name="check_in_start" value="{{ old('check_in_start', substr($setting->check_in_start, 0, 5)) }}" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm transition-all hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                @error('check_in_start') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Batas Masuk</label>
                <input type="time" name="check_in_deadline" value="{{ old('check_in_deadline', substr($setting->check_in_deadline, 0, 5)) }}" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm transition-all hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                @error('check_in_deadline') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Batas Verifikasi Masuk</label>
                <input type="time" name="check_in_verification_deadline" value="{{ old('check_in_verification_deadline', substr($setting->check_in_verification_deadline, 0, 5)) }}" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm transition-all hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                @error('check_in_verification_deadline') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Mulai Pulang</label>
                <input type="time" name="check_out_start" value="{{ old('check_out_start', substr($setting->check_out_start, 0, 5)) }}" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm transition-all hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                @error('check_out_start') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Toleransi Pulang</label>
                <input type="number" name="check_out_tolerance_minutes" value="{{ old('check_out_tolerance_minutes', $setting->check_out_tolerance_minutes) }}" min="0" max="60" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm transition-all hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                @error('check_out_tolerance_minutes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Toleransi Telat</label>
                <input type="number" name="late_tolerance_minutes" value="{{ old('late_tolerance_minutes', $setting->late_tolerance_minutes) }}" min="0" max="120" required
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm transition-all hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                @error('late_tolerance_minutes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="border-t border-gray-100 p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <label class="flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-700">
                <input type="checkbox" name="require_check_in_photo" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" @checked(old('require_check_in_photo', $setting->require_check_in_photo))>
                Wajib foto masuk
            </label>
            <label class="flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-700">
                <input type="checkbox" name="require_check_out_photo" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" @checked(old('require_check_out_photo', $setting->require_check_out_photo))>
                Wajib foto pulang
            </label>
            <label class="flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-700">
                <input type="checkbox" name="whatsapp_enabled" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" @checked(old('whatsapp_enabled', $setting->whatsapp_enabled))>
                Aktifkan WhatsApp
            </label>
        </div>

        <div class="border-t border-gray-100 p-5 sm:p-6">
            <p class="mb-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Koneksi WhatsApp (Fonnte)</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Provider</label>
                    <select name="whatsapp_provider" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm transition-all hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="fonnte" @selected(old('whatsapp_provider', $setting->whatsapp_provider ?? 'fonnte') === 'fonnte')>Fonnte</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Kode Negara</label>
                    <input type="text" name="whatsapp_country_code" value="{{ old('whatsapp_country_code', $setting->whatsapp_country_code ?? '62') }}"
                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm transition-all hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Endpoint API</label>
                    <input type="url" name="whatsapp_api_url" value="{{ old('whatsapp_api_url', $setting->whatsapp_api_url ?? 'https://api.fonnte.com/send') }}"
                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm transition-all hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div x-data="{ showToken: false }">
                    <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Token API</label>
                    <div class="relative">
                        <input :type="showToken ? 'text' : 'password'" name="whatsapp_token" value="{{ old('whatsapp_token', $setting->whatsapp_token) }}"
                               placeholder="Token dari dashboard Fonnte"
                               class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 pr-12 text-sm text-gray-800 transition-all hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <button type="button" @click="showToken = !showToken"
                                :title="showToken ? 'Sembunyikan token' : 'Lihat token'"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-blue-600">
                            <svg x-show="!showToken" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg x-show="showToken" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Nomor tujuan diambil dari nomor HP orang tua siswa. Isi angka saja, contoh 08123456789.</p>
        </div>

        <div class="border-t border-gray-100 p-5 sm:p-6">
            <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Mode Notifikasi WhatsApp</label>
            <select name="whatsapp_mode" x-model="mode" class="w-full sm:w-72 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm transition-all hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="lengkap" @selected(old('whatsapp_mode', $setting->whatsapp_mode) === 'lengkap')>Lengkap</option>
                <option value="hemat" @selected(old('whatsapp_mode', $setting->whatsapp_mode) === 'hemat')>Hemat</option>
                <option value="hanya_absen" @selected(old('whatsapp_mode', $setting->whatsapp_mode) === 'hanya_absen')>Hanya Absen</option>
            </select>
            <div class="mt-3 space-y-2 text-xs">
                @php($mode = old('whatsapp_mode', $setting->whatsapp_mode))
                <p class="mb-1 font-semibold text-gray-500 uppercase tracking-wide">Penjelasan tiap mode:</p>
                <div class="flex items-start gap-2 rounded-lg px-3 py-2 {{ $mode === 'lengkap' ? 'bg-indigo-50 font-semibold text-indigo-700' : 'text-gray-600' }}">
                    <span class="mt-0.5 shrink-0 {{ $mode === 'lengkap' ? 'text-indigo-600' : 'text-gray-400' }}">&#10003;</span>
                    <div><span class="font-semibold">Lengkap</span> — kirim notif setiap absen masuk & pulang siswa.</div>
                </div>
                <div class="flex items-start gap-2 rounded-lg px-3 py-2 {{ $mode === 'hemat' ? 'bg-indigo-50 font-semibold text-indigo-700' : 'text-gray-600' }}">
                    <span class="mt-0.5 shrink-0 {{ $mode === 'hemat' ? 'text-indigo-600' : 'text-gray-400' }}">&#10003;</span>
                    <div><span class="font-semibold">Hemat</span> — kirim notif untuk terlambat, pulang cepat/kegiatan, keputusan izin, dan siswa tidak hadir.</div>
                </div>
                <div class="flex items-start gap-2 rounded-lg px-3 py-2 {{ $mode === 'hanya_absen' ? 'bg-indigo-50 font-semibold text-indigo-700' : 'text-gray-600' }}">
                    <span class="mt-0.5 shrink-0 {{ $mode === 'hanya_absen' ? 'text-indigo-600' : 'text-gray-400' }}">&#10003;</span>
                    <div><span class="font-semibold">Hanya Absen</span> — hanya kirim notif siswa yang tidak tercatat hadir, semua notif masuk/pulang/telat dimatikan.</div>
                </div>
                <p class="pt-1 text-gray-500">Notifikasi siswa tidak hadir terkirim otomatis begitu lewat jam verifikasi absen.</p>
            </div>
        </div>

        <div class="border-t border-gray-100 p-5 sm:p-6 space-y-4">
            <div x-show="mode !== 'hanya_absen'">
                <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Template WhatsApp Masuk</label>
                <textarea name="check_in_message_template" rows="3"
                          class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm transition-all hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('check_in_message_template', $setting->check_in_message_template) }}</textarea>
                <p class="mt-2 text-xs text-gray-400">Variabel: {student}, {class}, {date}, {time}, {status}</p>
            </div>
            <div x-show="mode !== 'hanya_absen'">
                <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Template WhatsApp Pulang</label>
                <textarea name="check_out_message_template" rows="3"
                          class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm transition-all hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('check_out_message_template', $setting->check_out_message_template) }}</textarea>
                <p class="mt-2 text-xs text-gray-400">Variabel: {student}, {class}, {date}, {time}, {status}</p>
            </div>
            <div x-show="mode === 'hanya_absen'">
                <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Template WhatsApp Tidak Hadir</label>
                <textarea name="absent_message_template" rows="3"
                          class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm transition-all hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('absent_message_template', $setting->absent_message_template) }}</textarea>
                <p class="mt-2 text-xs text-gray-400">Variabel: {student}, {class}, {date}</p>
            </div>
        </div>

        <div class="border-t border-gray-100 p-5 sm:p-6 flex justify-end">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700">
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/tom-select/tom-select.min.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('vendor/tom-select/tom-select.complete.min.js') }}"></script>
<script>
    (function() {
        function init() {
            if (typeof window.TomSelect === 'undefined') {
                return setTimeout(init, 20);
            }
            ['#whatsapp_provider', '#whatsapp_mode'].forEach(function(sel) {
                var el = document.querySelector(sel);
                if (el && !el.tomselect) {
                    new window.TomSelect(el, {
                        create: false,
                        maxOptions: null,
                        placeholder: sel === '#whatsapp_mode' ? 'Pilih Mode' : 'Pilih Provider',
                    });
                }
            });
        }
        init();
    })();
</script>
@endpush
@endsection
