{{-- resources/views/super_admin/institutions/index.blade.php --}}
@extends('layouts.super_admin')

@section('title', __('Manajemen Instansi'))
@section('header', __('Manajemen Instansi'))

@section('content')
<div class="space-y-8 pb-8">
    <!-- Header Section -->
    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ __('Daftar Instansi') }}</h1>
                <p class="mt-1 text-sm text-gray-500 font-medium">{{ __('Kelola data seluruh institusi yang terdaftar dalam sistem.') }}</p>
            </div>
                <a href="{{ route('super-admin.institutions.trash') }}" 
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-black text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all duration-200">
                    Trash
                </a>
                <a href="{{ route('super-admin.institutions.create') }}" 
                   class="col-span-2 sm:col-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-black text-white bg-purple-600 rounded-xl hover:bg-purple-700 transition-all duration-200">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Tambah Instansi') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-600 uppercase tracking-widest">{{ __('Instansi') }}</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-600 uppercase tracking-widest">{{ __('Email') }}</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-600 uppercase tracking-widest">{{ __('Alamat') }}</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-600 uppercase tracking-widest">{{ __('Status') }}</th>
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-600 uppercase tracking-widest">{{ __('Statistik') }}</th>
                        <th class="px-6 py-4 text-right text-[10px] font-bold text-gray-600 uppercase tracking-widest">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($institutions as $institution)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
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
                        <td class="px-6 py-4 text-sm text-gray-700 font-medium">
                            {{ $institution->email }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                            {{ $institution->address ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($institution->status === 'active')
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">{{ __('Aktif') }}</span>
                            @elseif($institution->status === 'suspended')
                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">{{ __('Suspended') }}</span>
                            @elseif($institution->status === 'maintenance')
                                <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold">{{ __('Maintenance') }}</span>
                            @elseif($institution->status === 'expired')
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-bold">{{ __('Expired') }}</span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-bold">{{ $institution->status }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-[10px] font-bold">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    {{ $institution->students_count }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-[10px] font-bold">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ $institution->teachers_count }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-violet-50 text-violet-700 rounded-lg text-[10px] font-bold">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    {{ $institution->admins_count }}
                                </span>
                            </div>
                        </td>
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
                                <form action="{{ route('super-admin.institutions.destroy', $institution->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Yakin hapus instansi ini?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 font-medium">{{ __('Belum ada data instansi.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
