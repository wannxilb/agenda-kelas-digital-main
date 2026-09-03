{{-- resources/views/admin/activities/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Semua Aktivitas')
@section('header', 'Semua Aktivitas')

@section('content')
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
    <h2 class="text-xl font-bold text-gray-900 mb-6">Log Aktivitas Agenda</h2>
    
    <div class="space-y-4">
        @forelse($activities as $activity)
        <div class="flex items-center p-4 rounded-2xl bg-gray-50 hover:bg-gray-100 transition-all border border-transparent hover:border-gray-200">
            <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center shadow-sm mr-4 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-base font-bold text-gray-900">{{ $activity->title }}</p>
                <p class="text-sm text-gray-500">
                    <span class="font-bold text-blue-600">{{ $activity->class->name ?? 'Kelas N/A' }}</span> • 
                    {{ $activity->teacher->name ?? 'Guru N/A' }}
                </p>
            </div>
            <div class="text-right ml-4">
                <p class="text-sm font-bold text-gray-900">{{ $activity->created_at->format('d M Y') }}</p>
                <p class="text-xs text-gray-500">{{ $activity->created_at->format('H:i') }}</p>
            </div>
        </div>
        @empty
        <p class="text-gray-500 text-center py-12 italic">Belum ada aktivitas yang tercatat.</p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $activities->links() }}
    </div>
</div>
@endsection
