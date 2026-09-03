<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Presensi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', 'Helvetica', sans-serif; font-size: 11px; line-height: 1.4; color: #333; }

        .page { padding: 30px 25px; }

        /* Header */
        .header { text-align: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 3px solid #1e40af; }
        .header h1 { font-size: 16px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #1e40af; margin-bottom: 4px; }
        .header .subtitle { font-size: 11px; color: #6b7280; font-weight: 500; }
        .header .school { font-size: 13px; font-weight: 700; color: #111827; margin-bottom: 2px; }

        /* Info Row */
        .info-row { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-row td { padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; font-size: 10px; color: #6b7280; }
        .info-row td strong { color: #111827; font-weight: 700; }

        /* Summary Cards */
        .summary-cards { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .summary-cards td { padding: 8px 10px; border: 1px solid #e5e7eb; text-align: center; vertical-align: middle; }
        .summary-card .label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; margin-bottom: 2px; }
        .summary-card .value { font-size: 14px; font-weight: 800; }
        .val-total { color: #111827; }
        .val-present { color: #059669; }
        .val-absent { color: #dc2626; }
        .val-sick { color: #ea580c; }
        .val-excused { color: #0284c7; }
        .val-late { color: #d97706; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #1e40af; color: #fff; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 7px 8px; text-align: left; }
        th:first-child { border-radius: 3px 0 0 0; }
        th:last-child { border-radius: 0 3px 0 0; }
        td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; vertical-align: middle; }
        tr:nth-child(even) { background: #f9fafb; }
        tr:hover { background: #f3f4f6; }

        .status-badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
        .status-present { background: #d1fae5; color: #065f46; }
        .status-absent { background: #fee2e2; color: #991b1b; }
        .status-sick { background: #ffedd5; color: #9a3412; }
        .status-excused { background: #e0f2fe; color: #075985; }
        .status-late { background: #fef3c7; color: #92400e; }

        .date-group td { background: #eff6ff; padding: 5px 8px; font-size: 10px; font-weight: 700; color: #1e40af; border-bottom: 2px solid #bfdbfe; }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* Footer */
        .footer { width: 100%; border-collapse: collapse; margin-top: 25px; padding-top: 15px; border-top: 1px solid #e5e7eb; }
        .footer td { padding: 0; vertical-align: bottom; font-size: 9px; color: #9ca3af; }
        .footer-right { text-align: right; }
        .footer-right .signature-label { font-size: 9px; color: #6b7280; margin-bottom: 30px; }
        .footer-right .signature-name { font-size: 10px; font-weight: 700; color: #111827; border-top: 1px solid #333; padding-top: 3px; display: inline-block; min-width: 120px; }

        /* Page break */
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="school">{{ $class->name ?? 'Sekolah' }}</div>
            <h1>Laporan Presensi</h1>
            <div class="subtitle">Agenda Kelas Digital</div>
        </div>

        <table class="info-row">
            <tr>
                <td><strong>Kelas:</strong> {{ $class->name ?? '-' }}</td>
                <td><strong>Periode:</strong> {{ \Carbon\Carbon::parse(request('start_date', date('Y-m-01')))->translatedFormat('d M Y') }} — {{ \Carbon\Carbon::parse(request('end_date', date('Y-m-t')))->translatedFormat('d M Y') }}</td>
                <td><strong>Dicetak:</strong> {{ date('d/m/Y H:i') }}</td>
            </tr>
        </table>

        @php
            $total = $attendances->count();
            $present = $attendances->where('status', 'present')->count();
            $absent = $attendances->where('status', 'absent')->count();
            $sick = $attendances->where('status', 'sick')->count();
            $excused = $attendances->where('status', 'excused')->count();
            $late = $attendances->where('status', 'late')->count();
        @endphp

        <table class="summary-cards">
            <tr>
                <td>
                    <div class="label">Total</div>
                    <div class="value val-total">{{ $total }}</div>
                </td>
                <td>
                    <div class="label">Hadir</div>
                    <div class="value val-present">{{ $present }}</div>
                </td>
                <td>
                    <div class="label">Alpa</div>
                    <div class="value val-absent">{{ $absent }}</div>
                </td>
                <td>
                    <div class="label">Sakit</div>
                    <div class="value val-sick">{{ $sick }}</div>
                </td>
                <td>
                    <div class="label">Izin</div>
                    <div class="value val-excused">{{ $excused }}</div>
                </td>
                <td>
                    <div class="label">Telat</div>
                    <div class="value val-late">{{ $late }}</div>
                </td>
            </tr>
        </table>

        @php
            $statusLabels = [
                'present' => ['label' => 'Hadir', 'class' => 'status-present'],
                'absent'  => ['label' => 'Alpa', 'class' => 'status-absent'],
                'sick'    => ['label' => 'Sakit', 'class' => 'status-sick'],
                'late'    => ['label' => 'Telat', 'class' => 'status-late'],
                'excused' => ['label' => 'Izin', 'class' => 'status-excused'],
            ];
            $byDate = $attendances->groupBy(fn($a) => $a->date);
            foreach($byDate as &$records) {
                $records = $records->sortBy(fn($a) => strtolower($a->student->name ?? ''));
            }
        @endphp

        <table>
            <thead>
                <tr>
                    <th style="width:30px">No</th>
                    <th style="width:60px">NIS</th>
                    <th>Nama Siswa</th>
                    <th style="width:55px" class="text-center">Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($byDate as $date => $records)
                <tr class="date-group">
                    <td colspan="5">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                        <span style="font-weight:400;color:#6b7280;margin-left:6px">— {{ $records->count() }} data</span>
                    </td>
                </tr>
                @foreach($records as $att)
                @php $st = $statusLabels[$att->status] ?? ['label' => $att->status, 'class' => '']; @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $att->student->nis ?? '-' }}</td>
                    <td>{{ $att->student->name ?? '-' }}</td>
                    <td class="text-center"><span class="status-badge {{ $st['class'] }}">{{ $st['label'] }}</span></td>
                    <td>{{ $att->note ?? '-' }}</td>
                </tr>
                @endforeach
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
                    <div class="signature-name">{{ Auth::user()->name }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
