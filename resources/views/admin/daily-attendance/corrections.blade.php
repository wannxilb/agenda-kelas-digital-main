@extends('layouts.admin')

@section('title', 'Koreksi Absensi')
@section('header', 'Koreksi Absensi')

@section('content')
<div class="space-y-5">
    <div>
        <h1 class="text-xl font-black text-gray-900">Pengajuan Koreksi Absensi</h1>
        <p class="mt-1 text-sm text-gray-500">Data awal absensi tidak ditimpa. Setiap keputusan disimpan sebagai riwayat audit.</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="divide-y divide-gray-100">
            @forelse($corrections as $correction)
                <div class="p-5 space-y-3">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                        <div>
                            <p class="text-sm font-black text-gray-900">{{ $correction->student->name ?? '-' }} · {{ $correction->attendance->date?->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $correction->target_event === 'check_in' ? 'Absensi masuk' : 'Absensi pulang' }} · Status diminta: {{ str_replace('_', ' ', ucfirst($correction->requested_status)) }}</p>
                            <p class="mt-2 text-sm text-gray-700">{{ $correction->reason }}</p>
                            @if($correction->evidence_path)
                                <a class="text-xs font-bold text-blue-700" target="_blank" rel="noopener" href="{{ route('attendance.corrections.evidence', $correction) }}">Buka bukti tambahan</a>
                            @endif
                        </div>
                        <span class="rounded-lg px-2.5 py-1 text-xs font-black {{ ['approved' => 'bg-emerald-50 text-emerald-700', 'rejected' => 'bg-rose-50 text-rose-700'][$correction->status] ?? 'bg-amber-50 text-amber-700' }}">{{ strtoupper($correction->status) }}</span>
                    </div>
                    @if($correction->status === 'pending')
                        <div class="flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('admin.daily-attendance.corrections.review', $correction) }}">
                                @csrf
                                <input type="hidden" name="decision" value="approve">
                                <button class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white">Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('admin.daily-attendance.corrections.review', $correction) }}" class="flex gap-2">
                                @csrf
                                <input type="hidden" name="decision" value="reject">
                                <input required name="reviewer_note" placeholder="Alasan penolakan" class="rounded-lg border border-gray-200 px-3 py-2 text-xs">
                                <button class="rounded-lg bg-rose-600 px-3 py-2 text-xs font-bold text-white">Tolak</button>
                            </form>
                        </div>
                    @else
                        <p class="text-xs text-gray-500">Diproses oleh {{ $correction->reviewer->name ?? '-' }} pada {{ $correction->reviewed_at?->format('d/m/Y H:i') }}. {{ $correction->reviewer_note }}</p>
                    @endif
                </div>
            @empty
                <p class="p-8 text-center text-sm text-gray-500">Belum ada pengajuan koreksi.</p>
            @endforelse
        </div>
        <div class="p-4 border-t border-gray-100">{{ $corrections->links() }}</div>
    </div>
</div>
@endsection
