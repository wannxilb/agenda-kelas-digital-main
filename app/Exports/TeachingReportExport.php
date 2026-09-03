<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class TeachingReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection(): Collection
    {
        $rows = collect();
        foreach ($this->data['teachers'] as $i => $teacher) {
            $rows->push([
                $i + 1,
                $teacher->nip ?? '-',
                $teacher->name,
                $teacher->agendas_count ?? 0,
                $teacher->latest_agenda_date ? $teacher->latest_agenda_date->format('d/m/Y') : '-',
                $teacher->subjects->pluck('name')->implode(', ') ?: '-',
            ]);
        }
        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'NIP',
            'Nama Guru',
            'Total Jurnal',
            'Jurnal Terakhir',
            'Mata Pelajaran',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF4F46E5']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 18,
            'C' => 30,
            'D' => 12,
            'E' => 15,
            'F' => 40,
        ];
    }

    public function title(): string
    {
        return 'Rekap Jurnal Guru';
    }
}
