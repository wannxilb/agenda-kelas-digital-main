<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GuruAgendaReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection(): Collection
    {
        return collect($this->data['agendas'])->map(function ($agenda, int $index) {
            $schedule = $agenda->schedule;
            $scheduleTime = $schedule
                ? substr((string) $schedule->start_time, 0, 5) . ' - ' . substr((string) $schedule->end_time, 0, 5)
                : '-';

            return [
                $index + 1,
                optional($agenda->date)->format('d/m/Y') ?: '-',
                $agenda->class?->name ?: '-',
                $agenda->subject?->name ?: '-',
                $scheduleTime,
                $agenda->room ?: $schedule?->room ?: '-',
                $agenda->title,
                $agenda->status === 'published' ? 'Published' : 'Draft',
                trim(strip_tags((string) $agenda->description)) ?: '-',
                is_array($agenda->attachments) && count($agenda->attachments) > 0 ? count($agenda->attachments) . ' file' : '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Kelas',
            'Mata Pelajaran',
            'Jam Jadwal',
            'Ruangan',
            'Judul',
            'Status',
            'Deskripsi',
            'Lampiran',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2563EB']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 14,
            'C' => 18,
            'D' => 24,
            'E' => 16,
            'F' => 16,
            'G' => 32,
            'H' => 12,
            'I' => 55,
            'J' => 12,
        ];
    }

    public function title(): string
    {
        return 'Rekap Agenda';
    }
}
