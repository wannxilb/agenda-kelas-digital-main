<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Agenda Guru</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 11px; }
        h1 { margin: 0 0 4px; font-size: 18px; text-transform: uppercase; }
        .meta { margin-bottom: 14px; color: #4b5563; }
        .summary { margin-bottom: 12px; }
        .summary span { display: inline-block; margin-right: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        th { background: #2563eb; color: #fff; text-align: left; font-size: 10px; text-transform: uppercase; }
        td.center, th.center { text-align: center; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <h1>Rekap Agenda / Jurnal Mengajar</h1>
    <div class="meta">
        Guru: <strong>{{ $teacher->name }}</strong>
        @if($teacher->nip)
            | NIP: {{ $teacher->nip }}
        @endif
        <br>
        Periode: {{ $start_date->translatedFormat('d F Y') }} - {{ $end_date->translatedFormat('d F Y') }}
        | Kelas: {{ $class_name }}
        | Mapel: {{ $subject_name }}
        | Status: {{ $status_label }}
        <br>
        Dicetak: {{ $print_date }}
    </div>

    <div class="summary">
        <span>Total agenda: <strong>{{ $agendas->count() }}</strong></span>
        <span>Published: <strong>{{ $agendas->where('status', 'published')->count() }}</strong></span>
        <span>Draft: <strong>{{ $agendas->where('status', 'draft')->count() }}</strong></span>
    </div>

    <table>
        <thead>
            <tr>
                <th class="center" style="width: 28px;">No</th>
                <th style="width: 70px;">Tanggal</th>
                <th style="width: 90px;">Kelas</th>
                <th style="width: 110px;">Mapel</th>
                <th style="width: 70px;">Jam</th>
                <th>Judul & Deskripsi</th>
                <th style="width: 60px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($agendas as $agenda)
                @php
                    $schedule = $agenda->schedule;
                    $scheduleTime = $schedule ? substr((string) $schedule->start_time, 0, 5).' - '.substr((string) $schedule->end_time, 0, 5) : '-';
                    $description = trim(strip_tags((string) $agenda->description));
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ optional($agenda->date)->format('d/m/Y') }}</td>
                    <td>{{ $agenda->class?->name ?? '-' }}</td>
                    <td>{{ $agenda->subject?->name ?? '-' }}</td>
                    <td>{{ $scheduleTime }}</td>
                    <td>
                        <strong>{{ $agenda->title }}</strong>
                        @if($agenda->room || $schedule?->room)
                            <div class="muted">Ruangan: {{ $agenda->room ?: $schedule?->room }}</div>
                        @endif
                        <div>{{ $description ?: '-' }}</div>
                    </td>
                    <td>{{ $agenda->status === 'published' ? 'Published' : 'Draft' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center muted">Tidak ada agenda pada filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
