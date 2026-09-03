{{-- resources/views/admin/students/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Siswa')
@section('header', 'Edit Data Siswa')

@section('content')
@php
    $fieldBorder = fn (string $field): string => $errors->has($field) ? 'border-red-500' : 'border-gray-200';
    $classOptions = $classes->map(fn ($class) => [
        'value' => (string) $class->id,
        'label' => "{$class->name} ({$class->grade_level})",
        'grade_level' => $class->grade_level,
    ])->values()->all();
@endphp
<div id="student-edit-top" class="max-w-3xl mx-auto pb-12">
    <!-- Breadcrumb & Header -->
    <div class="mb-8">
        <nav class="flex text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.students.index') }}" class="hover:text-indigo-600 transition-colors">Siswa</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium">Edit Siswa</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">Edit Data Siswa</h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.students.update', $student) }}" method="POST">
            @csrf
            @method('PUT')
            
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
                            <input type="text" name="name" value="{{ old('name', $student->name) }}" required
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('name') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Gender -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <div class="flex space-x-6 mt-3">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="gender" value="L" {{ old('gender', $student->gender) == 'L' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500" required>
                                    <span class="ml-2 text-sm text-gray-700 font-medium">Laki-laki</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="gender" value="P" {{ old('gender', $student->gender) == 'P' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500" required>
                                    <span class="ml-2 text-sm text-gray-700 font-medium">Perempuan</span>
                                </label>
                            </div>
                            @error('gender') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Tempat Lahir -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $student->tempat_lahir) }}"
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('tempat_lahir') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                   placeholder="Contoh: Bandung">
                            @error('tempat_lahir') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Tanggal Lahir -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $student->tanggal_lahir) }}"
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('tanggal_lahir') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm">
                            @error('tanggal_lahir') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- No Telepon -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">No Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone', $student->phone) }}"
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('phone') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                   placeholder="Contoh: 081234567891">
                            @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">No WhatsApp Orang Tua</label>
                            <input type="text" name="parent_phone" value="{{ old('parent_phone', $student->parent_phone) }}"
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('parent_phone') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                   placeholder="Contoh: 081234567891">
                            @error('parent_phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email (Opsional)</label>
                            <input type="email" name="email" value="{{ old('email', $student->email) }}"
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('email') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                   placeholder="Contoh: siswa@school.com">
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            <p class="text-[10px] text-gray-500 mt-1">Digunakan untuk login.</p>
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
                            <input type="text" name="nis" value="{{ old('nis', $student->nis) }}" required
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('nis') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm">
                            @error('nis') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            <p class="text-[10px] text-gray-500 mt-1">Digunakan untuk login.</p>
                        </div>

                        <!-- NISN -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">NISN</label>
                            <input type="text" name="nisn" value="{{ old('nisn', $student->nisn) }}"
                                   class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('nisn') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                   placeholder="Contoh: 0098765432">
                            @error('nisn') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Kelas -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Kelas <span class="text-red-500">*</span></label>
                            <div class="relative" x-data="searchableSelect({
                                name: 'class_id',
                                value: @js((string) old('class_id', $student->class_id)),
                                placeholder: 'Pilih Kelas',
                                options: @js($classOptions),
                                onChange: window.syncGraduated
                            })">
                                <button type="button" @click="toggle()"
                                        class="w-full flex items-center justify-between gap-2 px-4 py-2.5 rounded-xl border bg-white text-left text-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        :class="open ? 'border-indigo-400 ring-2 ring-indigo-100' : '{{ $errors->has('class_id') ? 'border-red-400' : 'border-gray-200 hover:border-gray-300' }}'">
                                    <span class="truncate" :class="value ? 'text-gray-900' : 'text-gray-400'" x-text="selectedLabel"></span>
                                    <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div x-show="open" x-cloak @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="absolute left-0 right-0 mt-2 z-50 bg-white rounded-xl border border-gray-200 shadow-xl overflow-hidden">
                                    <div class="p-2">
                                        <div class="relative">
                                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"></path>
                                            </svg>
                                            <input x-model="search" type="text" placeholder="Cari kelas..."
                                                   class="w-full pl-9 pr-3 py-2 rounded-lg bg-gray-50 border border-gray-200 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400" />
                                        </div>
                                    </div>
                                    <div class="max-h-56 overflow-y-auto pb-2">
                                        <template x-for="opt in filteredOptions" :key="opt.value">
                                            <button type="button" @click="select(opt.value)"
                                                    class="w-full flex items-center justify-between gap-2 px-3 py-2 text-sm text-left transition-colors"
                                                    :class="opt.value === value ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-gray-700 hover:bg-gray-50'">
                                                <span class="truncate" x-text="opt.label"></span>
                                                <svg x-show="opt.value === value" class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        </template>
                                        <p x-show="filteredOptions.length === 0" class="px-3 py-3 text-sm text-gray-400 text-center">Kelas tidak ditemukan</p>
                                    </div>
                                </div>

                                <input type="hidden" :name="name" :value="value" />
                            </div>
                            @error('class_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status Siswa <span class="text-red-500">*</span></label>
                            <select name="status" id="status" required
                                    class="w-full text-sm @error('status') border-red-500 @enderror">
                                <option value="active" {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="graduated" id="status-graduated" {{ old('status', $student->status) == 'graduated' ? 'selected' : '' }}>Lulus</option>
                                <option value="inactive" {{ old('status', $student->status) == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                            </select>
                            @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Role Sekretaris -->
                        <div class="flex items-center space-x-3 p-4 bg-blue-50 rounded-xl border border-blue-100">
                            <div class="shrink-0">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <label class="flex items-center cursor-pointer">
                                    <div class="relative">
                                        <input type="checkbox" name="is_secretary" value="1" {{ $student->hasRole('sekretaris') ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </div>
                                    <span class="ml-3 text-sm font-bold text-gray-900">Sekretaris</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Ubah Password Baru</label>
                        <input type="text" name="password" value=""
                               class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('password') }} rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all text-sm"
                               placeholder="Biarkan kosong jika tidak ingin mengubah password">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-[10px] text-gray-500 mt-1">Kosongkan jika tidak ada perubahan.</p>
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
                                      placeholder="Nama jalan, nomor rumah, RT/RW...">{{ old('address', $student->address) }}</textarea>
                            @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- RT, RW, Kelurahan, Kecamatan -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <!-- RT -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">RT</label>
                                <input type="text" name="rt" value="{{ old('rt', $student->rt) }}"
                                       class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('rt') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                       placeholder="Contoh: 03">
                                @error('rt') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- RW -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">RW</label>
                                <input type="text" name="rw" value="{{ old('rw', $student->rw) }}"
                                       class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('rw') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                       placeholder="Contoh: 05">
                                @error('rw') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Kelurahan -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Kelurahan</label>
                                <input type="text" name="kelurahan" value="{{ old('kelurahan', $student->kelurahan) }}"
                                       class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('kelurahan') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                       placeholder="Contoh: Babakan Ciamis">
                                @error('kelurahan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Kecamatan -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Kecamatan</label>
                                <input type="text" name="kecamatan" value="{{ old('kecamatan', $student->kecamatan) }}"
                                       class="w-full px-4 py-2.5 bg-white border {{ $fieldBorder('kecamatan') }} rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                                       placeholder="Contoh: Sumur Bandung">
                                @error('kecamatan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                @if($student->created_at)
                <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-500 border border-gray-100 flex flex-col md:flex-row justify-between">
                    <div><span class="font-semibold text-gray-700">Terdaftar:</span> {{ $student->created_at->format('d M Y, H:i') }}</div>
                    <div><span class="font-semibold text-gray-700">Diperbarui:</span> {{ $student->updated_at->format('d M Y, H:i') }}</div>
                </div>
                @endif
            </div>
            
            <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.students.index') }}" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-100 transition-all">Batal</a>
                <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    const classOptions = @js($classOptions);

    function syncGraduated(classId) {
        const selected = classOptions.find((o) => String(o.value) === String(classId));
        const isXII = selected && selected.grade_level === 'XII';
        const graduatedOption = document.getElementById('status-graduated');

        if (!isXII) {
            if (window.statusTom && window.statusTom.getValue() === 'graduated') {
                window.statusTom.setValue('active');
            }
            if (graduatedOption) {
                graduatedOption.disabled = true;
                graduatedOption.style.display = 'none';
            }
        } else {
            if (graduatedOption) {
                graduatedOption.disabled = false;
                graduatedOption.style.display = 'block';
            }
        }
        if (window.statusTom) window.statusTom.refreshOptions(false);
    }

    window.syncGraduated = syncGraduated;

    function resetInitialStatusFocus() {
        if (window.statusTom) {
            window.statusTom.close();
            window.statusTom.blur();
            window.statusTom.control_input?.blur();
            window.statusTom.wrapper?.classList.remove('focus', 'dropdown-active', 'input-active');
        }

        const main = document.querySelector('main');
        if (main) main.scrollTop = 0;
        window.scrollTo(0, 0);
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('searchableSelect', (config) => ({
            open: false,
            search: '',
            name: config.name,
            value: config.value ?? '',
            placeholder: config.placeholder ?? 'Pilih...',
            options: config.options ?? [],
            get selectedLabel() {
                const found = this.options.find((o) => String(o.value) === String(this.value));
                return found ? found.label : this.placeholder;
            },
            get filteredOptions() {
                const q = this.search.trim().toLowerCase();
                if (!q) return this.options;
                return this.options.filter((o) => o.label.toLowerCase().includes(q));
            },
            toggle() {
                this.open = !this.open;
                if (this.open) this.search = '';
            },
            select(value) {
                this.value = value;
                this.open = false;
                this.search = '';
                if (config.onChange) config.onChange(value);
            },
        }));
    });

    document.addEventListener('DOMContentLoaded', function() {
        window.statusTom = new TomSelect('#status', {
            create: false,
            placeholder: 'Pilih Status',
            maxOptions: null,
        });

        syncGraduated(@js((string) old('class_id', $student->class_id)));

        requestAnimationFrame(() => {
            resetInitialStatusFocus();
            setTimeout(resetInitialStatusFocus, 50);
            setTimeout(resetInitialStatusFocus, 250);
        });
    });

    window.addEventListener('pageshow', () => {
        setTimeout(resetInitialStatusFocus, 0);
    });
</script>
@endpush
@endsection
