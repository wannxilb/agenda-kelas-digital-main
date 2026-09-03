{{-- resources/views/teacher-status/report-pdf.blade.php --}}
{{-- View PDF rekap izin/tugas luar — dipakai bersama wakasek & admin. --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Izin / Sakit / Tugas Luar</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 17px;
        }
        .header p {
            margin: 4px 0;
            color: #666;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        td.num, th.num {
            text-align: center;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAP IZIN / SAKIT / TUGAS LUAR GURU</h1>
        <p>Agenda Kelas Digital</p>
        <p>Periode: {{ $monthName }} {{ $year }}</p>
        <p>Hanya pengajuan yang disetujui</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="num" style="width: 6%">No</th>
                <th>Nama Guru</th>
                <th class="num" style="width: 14%">Izin (hari)</th>
                <th class="num" style="width: 14%">Sakit (hari)</th>
                <th class="num" style="width: 18%">Tugas Luar (hari)</th>
                <th class="num" style="width: 12%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
            <tr>
                <td class="num">{{ $i + 1 }}</td>
                <td>{{ $row['teacher']?->name ?? '-' }}</td>
                <td class="num">{{ $row['izin_days'] }}</td>
                <td class="num">{{ $row['sakit_days'] }}</td>
                <td class="num">{{ $row['tugas_days'] }}</td>
                <td class="num">{{ $row['total_days'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; color:#999;">Tidak ada data untuk periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <strong>Ringkasan:</strong>
        <p>Total Guru: {{ $rows->count() }} | Total Izin: {{ $totalIzin }} hari | Total Sakit: {{ $totalSakit }} hari | Total Tugas Luar: {{ $totalTugas }} hari | Total Keseluruhan: {{ $totalDays }} hari</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="footer">
        Dicetak oleh: {{ Auth::user()->name }}
    </div>
</body>
</html>
