<!DOCTYPE html>
<html>
<head>
    <title>Laporan Presensi — {{ $class->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; font-size: 11px; color: #374151; padding: 40px; }
        .header { margin-bottom: 30px; border-bottom: 2px solid #e5e7eb; padding-bottom: 15px; }
        .header h2 { font-size: 18px; font-weight: 900; color: #111827; margin-bottom: 4px; }
        .header p { font-size: 11px; color: #6b7280; }
        .meta { display: flex; gap: 30px; margin-bottom: 25px; }
        .meta-item { font-size: 11px; color: #6b7280; }
        .meta-item strong { color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f9fafb; text-align: left; padding: 8px 10px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; border-bottom: 2px solid #e5e7eb; }
        td { padding: 8px 10px; border-bottom: 1px solid #f3f4f6; font-size: 11px; }
        tr:nth-child(even) { background-color: #fafafa; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Presensi</h2>
        <p>Kelas {{ $class->name }} @if($academicYear) — {{ $academicYear->name }} Semester {{ $academicYear->semester }} @endif</p>
    </div>

    <div class="meta">
        <div class="meta-item"><strong>Periode:</strong> {{ $periodLabel }}</div>
        <div class="meta-item"><strong>Total Siswa:</strong> {{ $students->count() }}</div>
        <div class="meta-item"><strong>Dicetak:</strong> {{ now()->translatedFormat('d M Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Hadir</th>
                <th>Sakit</th>
                <th>Izin</th>
                <th>Telat</th>
                <th>Alpha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            @php
                $counts = $studentCounts[$student->id] ?? ['present' => 0, 'sick' => 0, 'excused' => 0, 'late' => 0, 'absent' => 0];
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student->nis ?? '-' }}</td>
                <td>{{ $student->name }}</td>
                <td>{{ $counts['present'] }}</td>
                <td>{{ $counts['sick'] }}</td>
                <td>{{ $counts['excused'] }}</td>
                <td>{{ $counts['late'] }}</td>
                <td>{{ $counts['absent'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dibuat secara otomatis oleh Sistem Agenda Kelas Digital
    </div>
</body>
</html>
