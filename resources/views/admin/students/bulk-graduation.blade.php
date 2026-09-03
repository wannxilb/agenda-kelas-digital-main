{{-- resources/views/admin/students/bulk-graduation.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelulusan Massal')
@section('header', 'Kelulusan Massal Siswa')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Proses Kelulusan Serentak Kelas XII</h3>
            <p class="text-sm text-gray-500 mt-1">Pilih kelas atau pilih individual siswa yang akan diluluskan. Secara default, seluruh siswa kelas XII akan terpilih.</p>
        </div>
    </div>

    @if($classes->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-red-600">Tidak Ada Kelas XII</h3>
            <p class="text-gray-500 mt-1">Tidak ditemukan kelas dengan tingkat XII. Pastikan tingkat kelas sudah diatur dengan benar.</p>
        </div>
    @else
        <div x-data="{ openClass: null }">
            <form action="{{ route('admin.students.process-bulk-graduation') }}" method="POST">
                @csrf
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div class="flex items-center">
                            <input type="checkbox" id="selectAll" class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500" checked>
                            <label for="selectAll" class="ml-3 text-sm font-medium text-gray-700 cursor-pointer">Pilih Semua Kelas ({{ $totalStudents }} siswa)</label>
                        </div>
                        <div class="text-sm text-gray-500 italic hidden sm:block">
                            Klik "Lihat Siswa" untuk membatalkan pilihan individu
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Pilih</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kelas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wali Kelas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Siswa Aktif</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($classes as $class)
                                    <!-- Class Row -->
                                    <tr class="hover:bg-blue-50/30 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" class="class-checkbox w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500" data-class-id="{{ $class->id }}" checked>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">{{ $class->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ $class->homeroomTeacher->name ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">{{ $class->students->count() }} Siswa</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <button type="button" @click="openClass === {{ $class->id }} ? openClass = null : openClass = {{ $class->id }}" class="text-blue-600 hover:text-blue-900 text-sm font-bold transition-colors inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg">
                                                <span x-text="openClass === {{ $class->id }} ? 'Sembunyikan' : 'Lihat Siswa'"></span>
                                                <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': openClass === {{ $class->id }}}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Students Accordion Row -->
                                    <tr x-show="openClass === {{ $class->id }}" x-cloak x-transition.opacity class="bg-gray-50/50">
                                        <td colspan="5" class="p-0 border-b border-gray-200 shadow-inner">
                                            <div class="p-4 pl-6 md:pl-24 bg-linear-to-r from-gray-50 to-white border-l-4 border-blue-400">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                                    @foreach($class->students as $student)
                                                    <label class="flex items-center p-3 bg-white border border-gray-200 rounded-xl shadow-sm hover:border-blue-300 cursor-pointer transition-all hover:shadow-md group">
                                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="student-checkbox student-class-{{ $class->id }} w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition-colors" data-class-id="{{ $class->id }}" checked>
                                                        <div class="ml-3">
                                                            <p class="text-sm font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">{{ $student->name }}</p>
                                                            <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $student->nis }}</p>
                                                        </div>
                                                    </label>
                                                    @endforeach
                                                    @if($class->students->isEmpty())
                                                    <div class="col-span-full p-6 text-center text-gray-500 text-sm border-2 border-dashed border-gray-200 rounded-xl bg-white">
                                                        Tidak ada siswa aktif di kelas ini.
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                        <a href="{{ route('admin.students.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-white font-medium transition-colors">
                            Batal
                        </a>
                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin meluluskan siswa yang dipilih? Siswa yang lulus tidak akan bisa login kembali.')"
                                class="px-6 py-2 bg-linear-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-indigo-700 shadow-md transition-all">
                            Proses Kelulusan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const classCheckboxes = document.querySelectorAll('.class-checkbox');
        const studentCheckboxes = document.querySelectorAll('.student-checkbox');

        // 1. Master Checkbox change
        selectAll?.addEventListener('change', function() {
            const isChecked = this.checked;
            classCheckboxes.forEach(cb => cb.checked = isChecked);
            studentCheckboxes.forEach(cb => cb.checked = isChecked);
        });

        // 2. Class Checkbox change
        classCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const classId = this.getAttribute('data-class-id');
                const isChecked = this.checked;
                const studentsInClass = document.querySelectorAll(`.student-class-${classId}`);
                studentsInClass.forEach(studentCb => studentCb.checked = isChecked);
                updateMasterCheckbox();
            });
        });

        // 3. Student Checkbox change
        studentCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const classId = this.getAttribute('data-class-id');
                updateClassCheckbox(classId);
                updateMasterCheckbox();
            });
        });

        function updateClassCheckbox(classId) {
            const classCb = document.querySelector(`.class-checkbox[data-class-id="${classId}"]`);
            const studentsInClass = document.querySelectorAll(`.student-class-${classId}`);
            const checkedStudents = document.querySelectorAll(`.student-class-${classId}:checked`);
            
            if (classCb) {
                classCb.checked = studentsInClass.length > 0 && studentsInClass.length === checkedStudents.length;
                classCb.indeterminate = checkedStudents.length > 0 && checkedStudents.length < studentsInClass.length;
            }
        }

        function updateMasterCheckbox() {
            if (!selectAll) return;
            const totalStudents = studentCheckboxes.length;
            const checkedStudents = document.querySelectorAll('.student-checkbox:checked').length;
            
            selectAll.checked = totalStudents > 0 && totalStudents === checkedStudents;
            selectAll.indeterminate = checkedStudents > 0 && checkedStudents < totalStudents;
        }
    });
</script>
@endpush
@endsection
