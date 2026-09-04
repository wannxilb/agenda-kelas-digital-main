{{-- resources/views/admin/settings.blade.php --}}
@extends('layouts.admin')

@section('title', __('Pengaturan Admin'))
@section('header', 'Pengaturan Sistem')

@section('content')
<div class="max-w-4xl mx-auto pb-12" x-data="settingsPage">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                {{ __('Informasi Sekolah') }}
            </h2>

            <form action="{{ route(Auth::user()->hasRole('super_admin') ? 'super-admin.settings.update' : 'admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" id="recurring_activities_input" name="recurring_activities" :value="JSON.stringify(activities)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Nama Sekolah') }}</label>
                        <input type="text" name="school_name" value="{{ old('school_name', $settings['school_name'] ?? 'SMK Digital') }}" required
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all text-sm font-medium">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Tahun Ajaran Aktif') }}</label>
                        <select name="academic_year" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all text-sm font-medium">
                            <option value="">{{ __('-- Pilih Tahun Ajaran --') }}</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->name }}" {{ (old('academic_year', $settings['academic_year'] ?? '') == $year->name) ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Semester') }}</label>
                        <select name="semester" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all text-sm font-medium">
                            <option value="Ganjil" {{ (old('semester', $settings['semester'] ?? '') == 'Ganjil') ? 'selected' : '' }}>{{ __('Ganjil') }}</option>
                            <option value="Genap" {{ (old('semester', $settings['semester'] ?? '') == 'Genap') ? 'selected' : '' }}>{{ __('Genap') }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Alamat Sekolah') }}</label>
                        <textarea name="school_address" rows="3"
                                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all text-sm font-medium">{{ old('school_address', $settings['school_address'] ?? '') }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg mr-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            {{ __('Logo & Favicon') }}
                        </h2>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Logo Sekolah') }}</label>
                        <p class="text-xs text-gray-500 mb-2">{{ __('Format: JPG, PNG, atau SVG. Maks 2MB.') }}</p>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden bg-gray-50 shrink-0">
                                @if($institution && $institution->logo)
                                    <img src="{{ asset('storage/' . $institution->logo) }}" alt="Logo" class="w-full h-full object-contain">
                                @else
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <input type="file" name="logo" accept="image/*"
                                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-all">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Favicon') }}</label>
                        <p class="text-xs text-gray-500 mb-2">{{ __('Format: ICO, PNG, atau SVG. Maks 1MB.') }}</p>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden bg-gray-50 shrink-0">
                                @if($institution && $institution->favicon)
                                    <img src="{{ asset('storage/' . $institution->favicon) }}" alt="Favicon" class="w-full h-full object-contain">
                                @else
                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <input type="file" name="favicon" accept="image/*"
                                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition-all">
                            </div>
                        </div>
                    </div>

                    @php
                        $schoolLocationEnabled = old('school_location_enabled', $settings['school_location_enabled'] ?? $settings['agenda_location_enabled'] ?? '0');
                        $schoolLocationLatitude = old('school_location_latitude', $settings['school_location_latitude'] ?? $settings['agenda_location_latitude'] ?? '');
                        $schoolLocationLongitude = old('school_location_longitude', $settings['school_location_longitude'] ?? $settings['agenda_location_longitude'] ?? '');
                        $schoolLocationRadius = old('school_location_radius_meters', $settings['school_location_radius_meters'] ?? $settings['agenda_location_radius_meters'] ?? '100');
                    @endphp

                    <div class="md:col-span-2 mt-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg mr-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            {{ __('Waktu Operasional Pengisian Agenda & Presensi') }}
                        </h2>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">{{ __('Hari Operasional') }}</label>
                        @php
                            $selectedDaysRaw = old('operational_days', $settings['operational_days'] ?? '1,2,3,4,5');
                            $selectedDays = is_array($selectedDaysRaw)
                                ? array_map('strval', $selectedDaysRaw)
                                : explode(',', (string) $selectedDaysRaw);
                            $days = [
                                '1' => __('Senin'),
                                '2' => __('Selasa'),
                                '3' => __('Rabu'),
                                '4' => __('Kamis'),
                                '5' => __('Jumat'),
                                '6' => __('Sabtu'),
                                '7' => __('Minggu')
                            ];
                        @endphp
                        <div class="flex flex-wrap gap-4">
                            @foreach($days as $val => $label)
                            <label class="flex items-center gap-2 cursor-pointer p-3 border border-gray-200 rounded-xl hover:bg-indigo-50 transition-all">
                                <input type="checkbox" name="operational_days[]" value="{{ $val }}" 
                                       {{ in_array((string)$val, $selectedDays) ? 'checked' : '' }}
                                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Jam Buka') }}</label>
                        <input type="time" name="operational_start_time" value="{{ old('operational_start_time', $settings['operational_start_time'] ?? '06:30') }}" required
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-medium">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Jam Tutup') }}</label>
                        <input type="time" name="operational_end_time" value="{{ old('operational_end_time', $settings['operational_end_time'] ?? '16:00') }}" required
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-medium">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Buka Akses Sementara (Override) Hingga') }}</label>
                        <p class="text-xs text-gray-500 mb-2">{{ __('Jika ada pengguna yang meminta izin input di luar jam operasional, atur batas waktu pembukaan akses sementara di sini. Kosongkan untuk menutup akses kembali.') }}</p>
                        <input type="datetime-local" name="operational_override_until" value="{{ old('operational_override_until', $settings['operational_override_until'] ?? '') }}"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 transition-all text-sm font-medium">
                    </div>

                    <div class="md:col-span-2 mt-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg mr-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            {{ __('Jam Pelajaran (Kalender Jadwal)') }}
                        </h2>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Daftar Jam Pelajaran') }}</label>
                        <p class="text-xs text-gray-500 mb-2">{{ __('Satu jam per baris dengan format HH:MM. Untuk baris istirahat, tambahkan label setelah tanda |, contoh: 12:00|Istirahat & ISHOMA.') }}</p>
                        <textarea name="schedule_time_slots" rows="12"
                                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all text-sm font-medium font-mono">{{ old('schedule_time_slots', \App\Models\Setting::scheduleTimeSlotsRaw()) }}</textarea>
                        @error('schedule_time_slots') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2 mt-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">Kegiatan Rutin</h3>
                                <p class="text-xs text-gray-500">Kegiatan yang otomatis muncul di jadwal pada hari-hari tertentu (misal: Senin & Jumat 45 menit).</p>
                            </div>
                            <button type="button" @click="openModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah
                            </button>
                        </div>

                        @error('recurring_activities') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror

                        <template x-if="activities.length === 0">
                            <div class="text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                                <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="text-xs text-gray-400">Belum ada kegiatan rutin</p>
                            </div>
                        </template>

                        <div class="space-y-2">
                            <template x-for="(act, idx) in activities" :key="act.id">
                                <div class="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900" x-text="act.name"></p>
                                            <p class="text-xs text-gray-500">
                                                <span x-text="act.start_time"></span> · <span x-text="act.duration + ' menit'"></span> · <span x-text="act.days.map(d => dayLabel(d)).join(', ')"></span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="openModal(idx)" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button type="button" @click="removeActivity(idx)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="md:col-span-2 mt-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <div class="p-2 bg-amber-50 text-amber-600 rounded-lg mr-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 9c0 7-7.5 12-7.5 12S4.5 16 4.5 9a7.5 7.5 0 1115 0z"></path>
                                </svg>
                            </div>
                            {{ __('Wilayah Sekolah') }}
                        </h2>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 cursor-pointer p-4 border border-gray-200 rounded-xl bg-gray-50">
                            <input type="checkbox" name="school_location_enabled" value="1"
                                   id="agenda_location_enabled"
                                   {{ $schoolLocationEnabled == '1' ? 'checked' : '' }}
                                   class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                            <span>
                                <span class="block text-sm font-semibold text-gray-700">{{ __('Aktifkan batas wilayah sekolah') }}</span>
                                <span class="block text-xs text-gray-500 mt-0.5">{{ __('Agenda dan absensi harian harus dilakukan dalam radius lokasi sekolah.') }}</span>
                            </span>
                        </label>
                    </div>

                    <div class="md:col-span-2 overflow-hidden rounded-2xl border border-gray-200 bg-slate-950 shadow-sm">
                        <div class="grid grid-cols-1 lg:grid-cols-[1fr_280px]">
                            <div class="relative min-h-90">
                                <div id="agenda-location-map" class="absolute inset-0 z-0"></div>
                                <div class="absolute left-4 top-4 z-500 rounded-xl bg-white/95 px-4 py-3 shadow-lg backdrop-blur">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-amber-600">Area aktif</p>
                                    <p class="mt-1 text-sm font-bold text-gray-900"><span id="agenda-radius-preview">{{ $schoolLocationRadius }}</span> meter</p>
                                </div>
                                <button type="button" id="agenda-use-current-location"
                                        class="absolute bottom-4 left-4 z-500 inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-xs font-bold text-gray-800 shadow-lg transition-all hover:bg-amber-50 hover:text-amber-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v3m0 12v3m9-9h-3M6 12H3m15.364-6.364l-2.121 2.121M7.757 16.243l-2.121 2.121m12.728 0l-2.121-2.121M7.757 7.757 5.636 5.636"></path>
                                    </svg>
                                    Gunakan Lokasi Saat Ini
                                </button>
                            </div>

                            <div class="bg-white p-5">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Latitude Pusat') }}</label>
                                        <input type="number" step="any" name="school_location_latitude" id="agenda_location_latitude" value="{{ $schoolLocationLatitude }}"
                                               placeholder="-6.200000"
                                               class="w-full px-4 py-2.5 bg-gray-50 border rounded-xl focus:ring-2 focus:ring-amber-500 transition-all text-sm font-medium {{ $errors->has('school_location_latitude') ? 'border-red-500' : 'border-gray-200' }}">
                                        @error('school_location_latitude') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Longitude Pusat') }}</label>
                                        <input type="number" step="any" name="school_location_longitude" id="agenda_location_longitude" value="{{ $schoolLocationLongitude }}"
                                               placeholder="106.816666"
                                               class="w-full px-4 py-2.5 bg-gray-50 border rounded-xl focus:ring-2 focus:ring-amber-500 transition-all text-sm font-medium {{ $errors->has('school_location_longitude') ? 'border-red-500' : 'border-gray-200' }}">
                                        @error('school_location_longitude') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <div class="flex items-center justify-between gap-3 mb-2">
                                            <label class="block text-sm font-semibold text-gray-700">{{ __('Radius Diizinkan') }}</label>
                                            <span class="rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700"><span id="agenda-radius-label">{{ $schoolLocationRadius }}</span> m</span>
                                        </div>
                                        <input type="range" min="10" max="10000" step="10" id="agenda_location_radius_slider" value="{{ $schoolLocationRadius }}"
                                               class="w-full accent-amber-600">
                                        <input type="number" min="10" max="10000" name="school_location_radius_meters" id="agenda_location_radius_meters" value="{{ $schoolLocationRadius }}"
                                               class="mt-2 w-full px-4 py-2.5 bg-gray-50 border rounded-xl focus:ring-2 focus:ring-amber-500 transition-all text-sm font-medium {{ $errors->has('school_location_radius_meters') ? 'border-red-500' : 'border-gray-200' }}">
                                        @error('school_location_radius_meters') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div id="agenda-map-status" class="rounded-xl bg-gray-50 px-4 py-3 text-xs font-semibold text-gray-500">
                                        Klik peta atau geser marker untuk menentukan pusat wilayah.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 md:col-span-2">
                    <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">
                        {{ __('Simpan Pengaturan') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Kegiatan Rutin --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[200] overflow-y-auto" x-transition.opacity>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>
            <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all">
                <div class="bg-white px-6 pt-6 pb-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900" x-text="editIndex === null ? 'Tambah Kegiatan Rutin' : 'Edit Kegiatan Rutin'"></h3>
                </div>
                <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Kegiatan</label>
                        <input type="text" x-model="form.name" placeholder="cth: Upacara Bendera / Senam Pagi"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 transition-all text-sm font-medium">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Mulai</label>
                            <input type="time" x-model="form.start_time"
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 transition-all text-sm font-medium">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Durasi (menit)</label>
                            <select x-model="form.duration"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 transition-all text-sm font-medium">
                                <template x-for="d in [15, 30, 45, 60, 90, 120]" :key="d">
                                    <option :value="d" x-text="d + ' menit'"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Hari Pelaksanaan</label>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="d in dayOptions" :key="d.value">
                                <button type="button"
                                        @click="toggleDay(d.value)"
                                        class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors"
                                        :class="form.days.includes(d.value) ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                        x-text="d.label"></button>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 flex justify-end gap-2 border-t border-gray-100">
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                    <button type="button" @click="saveActivity()" class="px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-sm">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
<style>
    #agenda-location-map {
        min-height: 360px;
        background: #0f172a;
    }
    #agenda-location-map .leaflet-control-attribution {
        font-size: 10px;
    }
    #agenda-location-map .leaflet-control-zoom a {
        border: 0;
        color: #1f2937;
        font-weight: 800;
    }
    .agenda-location-marker {
        background: #d97706;
        border: 3px solid #fff;
        border-radius: 9999px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.35);
        height: 22px;
        width: 22px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('alpine:init', function () {
    Alpine.data('settingsPage', function () {
        return {
            activities: {!! json_encode(\App\Models\Setting::recurringActivities()) !!},
            showModal: false,
            editIndex: null,
            form: { id: '', name: '', start_time: '07:00', duration: 45, days: [] },
            dayOptions: [
                { value: 'Monday', label: 'Senin' },
                { value: 'Tuesday', label: 'Selasa' },
                { value: 'Wednesday', label: 'Rabu' },
                { value: 'Thursday', label: 'Kamis' },
                { value: 'Friday', label: "Jum'at" },
                { value: 'Saturday', label: 'Sabtu' }
            ],
            dayLabel(d) {
                var m = { Monday: 'Senin', Tuesday: 'Selasa', Wednesday: 'Rabu', Thursday: 'Kamis', Friday: "Jum'at", Saturday: 'Sabtu' };
                return m[d] || d;
            },
            openModal(index) {
                if (index === null || index === undefined) {
                    this.editIndex = null;
                    this.form = { id: 'act_' + Date.now(), name: '', start_time: '07:00', duration: 45, days: [] };
                } else {
                    this.editIndex = index;
                    var a = this.activities[index];
                    this.form = { id: a.id, name: a.name, start_time: a.start_time, duration: a.duration, days: a.days.slice() };
                }
                this.showModal = true;
            },
            toggleDay(day) {
                var i = this.form.days.indexOf(day);
                if (i >= 0) {
                    this.form.days.splice(i, 1);
                } else {
                    this.form.days.push(day);
                }
            },
            saveActivity() {
                if (!this.form.name || !this.form.start_time || this.form.days.length === 0) {
                    Swal.fire('Error', 'Lengkapi nama, jam mulai, dan minimal satu hari.', 'error');
                    return;
                }
                var clean = this.form.name.trim();
                if (!clean) {
                    Swal.fire('Error', 'Nama kegiatan wajib diisi.', 'error');
                    return;
                }
                var payload = {
                    id: this.form.id,
                    name: clean,
                    start_time: this.form.start_time,
                    duration: parseInt(this.form.duration, 10),
                    days: this.form.days.slice()
                };
                if (this.editIndex === null) {
                    this.activities.push(payload);
                } else {
                    this.activities[this.editIndex] = payload;
                }
                this.showModal = false;
            },
            removeActivity(index) {
                var self = this;
                Swal.fire({
                    title: 'Hapus Kegiatan?',
                    text: 'Yakin ingin menghapus kegiatan "' + this.activities[index].name + '"?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        self.activities.splice(index, 1);
                    }
                });
            }
        };
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof L === 'undefined') return;

    var latInput = document.getElementById('agenda_location_latitude');
    var lngInput = document.getElementById('agenda_location_longitude');
    var radiusInput = document.getElementById('agenda_location_radius_meters');
    var radiusSlider = document.getElementById('agenda_location_radius_slider');
    var radiusLabel = document.getElementById('agenda-radius-label');
    var radiusPreview = document.getElementById('agenda-radius-preview');
    var statusEl = document.getElementById('agenda-map-status');
    var useCurrentButton = document.getElementById('agenda-use-current-location');
    var enabledInput = document.getElementById('agenda_location_enabled');

    var defaultLat = -6.200000;
    var defaultLng = 106.816666;
    var lat = parseFloat(latInput.value) || defaultLat;
    var lng = parseFloat(lngInput.value) || defaultLng;
    var radius = parseInt(radiusInput.value, 10) || 100;

    var map = L.map('agenda-location-map', {
        zoomControl: true,
        scrollWheelZoom: true
    }).setView([lat, lng], latInput.value && lngInput.value ? 17 : 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    var markerIcon = L.divIcon({
        className: 'agenda-location-marker',
        iconSize: [22, 22],
        iconAnchor: [11, 11]
    });

    var marker = L.marker([lat, lng], {
        draggable: true,
        icon: markerIcon
    }).addTo(map);

    var circle = L.circle([lat, lng], {
        radius: radius,
        color: '#d97706',
        weight: 2,
        fillColor: '#f59e0b',
        fillOpacity: 0.18
    }).addTo(map);

    function setStatus(text, tone) {
        if (!statusEl) return;
        var classes = {
            neutral: 'rounded-xl bg-gray-50 px-4 py-3 text-xs font-semibold text-gray-500',
            ok: 'rounded-xl bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-700',
            warn: 'rounded-xl bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-700',
            error: 'rounded-xl bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700'
        };
        statusEl.className = classes[tone] || classes.neutral;
        statusEl.textContent = text;
    }

    function normalizeRadius(value) {
        var parsed = parseInt(value, 10);
        if (Number.isNaN(parsed)) parsed = 100;
        return Math.min(Math.max(parsed, 10), 10000);
    }

    function syncRadius(value) {
        radius = normalizeRadius(value);
        radiusInput.value = radius;
        radiusSlider.value = radius;
        radiusLabel.textContent = radius;
        radiusPreview.textContent = radius;
        circle.setRadius(radius);
    }

    function syncCenter(center, pan) {
        lat = Number(center.lat.toFixed(7));
        lng = Number(center.lng.toFixed(7));
        latInput.value = lat;
        lngInput.value = lng;
        marker.setLatLng([lat, lng]);
        circle.setLatLng([lat, lng]);
        if (pan) map.setView([lat, lng], Math.max(map.getZoom(), 17));
        setStatus('Titik pusat wilayah sudah diperbarui.', 'ok');
    }

    function syncFromInputs() {
        var inputLat = parseFloat(latInput.value);
        var inputLng = parseFloat(lngInput.value);
        if (Number.isNaN(inputLat) || Number.isNaN(inputLng)) return;
        syncCenter({ lat: inputLat, lng: inputLng }, true);
    }

    marker.on('dragend', function(event) {
        syncCenter(event.target.getLatLng(), false);
    });

    map.on('click', function(event) {
        syncCenter(event.latlng, true);
    });

    radiusInput.addEventListener('input', function() {
        syncRadius(this.value);
    });

    radiusSlider.addEventListener('input', function() {
        syncRadius(this.value);
    });

    latInput.addEventListener('change', syncFromInputs);
    lngInput.addEventListener('change', syncFromInputs);

    if (enabledInput) {
        enabledInput.addEventListener('change', function() {
            setTimeout(function() {
                map.invalidateSize();
            }, 150);
        });
    }

    if (useCurrentButton) {
        useCurrentButton.addEventListener('click', function() {
            if (!navigator.geolocation) {
                setStatus('Browser tidak mendukung deteksi lokasi.', 'error');
                return;
            }

            setStatus('Mengambil lokasi perangkat...', 'warn');
            navigator.geolocation.getCurrentPosition(function(position) {
                syncCenter({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                }, true);
            }, function() {
                setStatus('Lokasi perangkat tidak dapat diakses.', 'error');
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        });
    }

    syncRadius(radius);
    setTimeout(function() {
        map.invalidateSize();
    }, 250);
});
</script>
@endpush
