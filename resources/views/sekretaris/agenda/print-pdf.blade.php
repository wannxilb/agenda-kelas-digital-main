<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Agenda Kelas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', 'Helvetica', sans-serif; font-size: 11px; line-height: 1.4; color: #333; }

        .page { padding: 30px 25px; }

        .header { text-align: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 3px solid #1e40af; }
        .header h1 { font-size: 16px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #1e40af; margin-bottom: 4px; }
        .header .subtitle { font-size: 11px; color: #6b7280; font-weight: 500; }
        .header .school { font-size: 13px; font-weight: 700; color: #111827; margin-bottom: 2px; }

        .info-row { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-row td { padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; font-size: 10px; color: #6b7280; }
        .info-row td strong { color: #111827; font-weight: 700; }

        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th { background: #1e40af; color: #fff; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 7px 8px; text-align: left; }
        table.data th:first-child { border-radius: 3px 0 0 0; }
        table.data th:last-child { border-radius: 0 3px 0 0; }
        table.data td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; vertical-align: middle; }
        table.data tr:nth-child(even) { background: #f9fafb; }

        .text-center { text-align: center; }

        .footer { width: 100%; border-collapse: collapse; margin-top: 25px; padding-top: 15px; border-top: 1px solid #e5e7eb; }
        .footer td { padding: 0; vertical-align: bottom; font-size: 9px; color: #9ca3af; }
        .footer-right { text-align: right; }
        .footer-right .signature-label { font-size: 9px; color: #6b7280; margin-bottom: 30px; }
        .footer-right .signature-name { font-size: 10px; font-weight: 700; color: #111827; border-top: 1px solid #333; padding-top: 3px; display: inline-block; min-width: 120px; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="school">{{ \App\Models\Setting::get('school_name', $class->name ?? 'Sekolah') }}</div>
            <h1>Laporan Agenda Kelas</h1>
            <div class="subtitle">Agenda Kelas Digital</div>
        </div>

        <table class="info-row">
            <tr>
                <td><strong>Kelas:</strong> {{ $class->name ?? '-' }}</td>
                <td><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse(request('date', date('Y-m-d')))->translatedFormat('d M Y') }}</td>
                <td><strong>Dicetak:</strong> {{ date('d/m/Y H:i') }}</td>
            </tr>
        </table>

        <table class="data">
            <thead>
                <tr>
                    <th style="width:30px">No</th>
                    <th style="width:70px">Tanggal</th>
                    <th>Mata Pelajaran</th>
                    <th>Guru</th>
                    <th style="width:55px">Ruang</th>
                    <th>Judul / Materi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($agendas as $index => $agenda)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($agenda->date)->format('d/m/Y') }}</td>
                    <td>{{ $agenda->subject->name ?? 'Umum' }}</td>
                    <td>{{ $agenda->teacher->name ?? '-' }}</td>
                    <td>{{ $agenda->room ?? '-' }}</td>
                    <td>{{ $agenda->title }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="footer">
            <tr>
                <td>
                    Dicetak oleh: {{ Auth::user()->name }}<br>
                    {{ date('d F Y, H:i') }}
                </td>
                <td class="footer-right">
                    <div class="signature-label">Mengetahui,</div>
                    <div class="signature-name">{{ $class->homeroomTeacher->name ?? Auth::user()->name }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
