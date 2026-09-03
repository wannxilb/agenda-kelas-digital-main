<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeacherStatusReportExport implements FromCollection, WithColumnWidths, WithHeadings, WithStyles, WithTitle
{
    public function __construct(protected Collection $rows) {}

    public function collection(): Collection
    {
        $totalIzin = $this->rows->sum('izin_days');
        $totalSakit = $this->rows->sum('sakit_days');
        $totalTugas = $this->rows->sum('tugas_days');
        $totalDays = $this->rows->sum('total_days');

        $rows = $this->rows->map(function ($row, $i) {
            return [
                $i + 1,
                $row['teacher']?->name ?? '-',
                $row['izin_days'],
                $row['sakit_days'],
                $row['tugas_days'],
                $row['total_days'],
            ];
        });

        // Baris total — konsisten dengan ringkasan di ekspor PDF
        $rows->push([
            '',
            'TOTAL',
            $totalIzin,
            $totalSakit,
            $totalTugas,
            $totalDays,
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Guru',
            'Izin (hari)',
            'Sakit (hari)',
            'Tugas Luar (hari)',
            'Total',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1D4ED8']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 30,
            'C' => 12,
            'D' => 14,
            'E' => 18,
            'F' => 10,
        ];
    }

    public function title(): string
    {
        return 'Rekap Izin/Sakit/Tugas Luar';
    }
}
