@extends('layouts.super_admin')

@section('title', 'Hasil Pencarian')
@section('header', 'Hasil Pencarian untuk: "' . $query . '"')

@section('content')
<div class="space-y-6">
    {{-- Institutions --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800">Instansi <span class="text-gray-400 font-normal">({{ $institutions->count() }})</span></h3>
        </div>

        @if($institutions->count())
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-[10px] text-gray-500 uppercase tracking-widest bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 font-bold">Nama Instansi</th>
                            <th class="px-6 py-4 font-bold">Email</th>
                            <th class="px-6 py-4 font-bold hidden lg:table-cell">Alamat</th>
                            <th class="px-6 py-4 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($institutions as $institution)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-linear-to-br from-purple-100 to-indigo-100 text-purple-600 rounded-lg flex items-center justify-center font-bold text-sm shrink-0">
                                            {{ strtoupper(substr($institution->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900">{{ $institution->name }}</p>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">ID: {{ $institution->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $institution->email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 hidden lg:table-cell max-w-xs truncate">{{ $institution->address ?? '-' }}</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-3">
                                        <a href="{{ route('super-admin.institutions.show', $institution->id) }}" class="text-gray-400 hover:text-blue-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('super-admin.institutions.edit', $institution->id) }}" class="text-gray-400 hover:text-green-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="md:hidden divide-y divide-gray-100">
                @foreach($institutions as $institution)
                    <div class="p-4 space-y-2 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-linear-to-br from-purple-100 to-indigo-100 text-purple-600 rounded-lg flex items-center justify-center font-bold text-sm shrink-0">
                                {{ strtoupper(substr($institution->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-gray-900 text-sm truncate">{{ $institution->name }}</div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">ID: {{ $institution->id }}</div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500">{{ $institution->email }}</div>
                        @if($institution->address)
                            <div class="text-xs text-gray-400 truncate">{{ $institution->address }}</div>
                        @endif
                        <div class="flex items-center gap-3 pt-1">
                            <a href="{{ route('super-admin.institutions.show', $institution->id) }}" class="text-gray-400 hover:text-blue-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            <a href="{{ route('super-admin.institutions.edit', $institution->id) }}" class="text-gray-400 hover:text-green-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 text-center text-gray-400 text-sm">Tidak ada instansi dengan kata kunci "{{ $query }}".</div>
        @endif
    </div>

    {{-- Users --}}
    @if($users->count())
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-800">Pengguna <span class="text-gray-400 font-normal">({{ $users->flatten(1)->count() }})</span></h3>
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-[10px] text-gray-500 uppercase tracking-widest bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 font-bold">Nama</th>
                            <th class="px-6 py-4 font-bold">Email</th>
                            <th class="px-6 py-4 font-bold">Role</th>
                            <th class="px-6 py-4 font-bold hidden lg:table-cell">Instansi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $role => $roleUsers)
                            @foreach($roleUsers as $user)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 bg-linear-to-br from-gray-100 to-gray-200 text-gray-600 rounded-lg flex items-center justify-center font-bold text-sm shrink-0">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <p class="font-bold text-gray-900 truncate max-w-[200px]" title="{{ $user->name }}">{{ $user->name }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium whitespace-nowrap">{{ $user->email }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold
                                            @switch($role)
                                                @case('super_admin') bg-red-100 text-red-700 @break
                                                @case('admin') bg-blue-100 text-blue-700 @break
                                                @case('teacher') bg-green-100 text-green-700 @break
                                                @case('wali_kelas') bg-cyan-100 text-cyan-700 @break
                                                @case('sekretaris') bg-orange-100 text-orange-700 @break
                                                @case('siswa') bg-yellow-100 text-yellow-700 @break
                                                @case('wakasek') bg-indigo-100 text-indigo-700 @break
                                                @default bg-gray-100 text-gray-600
                                            @endswitch
                                        ">{{ str_replace(['_', 'super_admin', 'wali_kelas'], [' ', 'Super Admin', 'Wali Kelas'], $role) }}</span>
                                    </td>
                                    <td class="px-6 py-4 hidden lg:table-cell max-w-[200px] truncate text-sm text-gray-500" title="{{ $user->institution?->name ?? '-' }}">{{ $user->institution?->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="md:hidden divide-y divide-gray-100">
                @foreach($users as $role => $roleUsers)
                    @foreach($roleUsers as $user)
                        <div class="p-4 space-y-2 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-linear-to-br from-gray-100 to-gray-200 text-gray-600 rounded-lg flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-gray-900 text-sm truncate">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </div>
                                <span class="shrink-0 px-3 py-1 rounded-full text-[10px] font-bold
                                    @switch($role)
                                        @case('super_admin') bg-red-100 text-red-700 @break
                                        @case('admin') bg-blue-100 text-blue-700 @break
                                        @case('teacher') bg-green-100 text-green-700 @break
                                        @case('wali_kelas') bg-cyan-100 text-cyan-700 @break
                                        @case('sekretaris') bg-orange-100 text-orange-700 @break
                                        @case('siswa') bg-yellow-100 text-yellow-700 @break
                                        @case('wakasek') bg-indigo-100 text-indigo-700 @break
                                        @default bg-gray-100 text-gray-600
                                    @endswitch
                                ">{{ str_replace(['_', 'super_admin', 'wali_kelas'], [' ', 'Super Admin', 'Wali Kelas'], $role) }}</span>
                            </div>
                            @if($user->institution)
                                <div class="text-xs text-gray-400 truncate pl-[52px]">{{ $user->institution->name }}</div>
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif

    {{-- Empty State --}}
    @if($institutions->isEmpty() && $users->isEmpty())
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-10 text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <p class="text-gray-500 text-sm font-medium">Tidak ditemukan hasil untuk kata kunci <strong>"{{ $query }}"</strong>.</p>
            <p class="text-gray-400 text-xs mt-1">Coba gunakan kata kunci lain yang lebih spesifik.</p>
        </div>
    @endif
</div>
@endsection
