<?php

namespace App\Exports;

use App\Models\GradeAssignment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradeAssignmentExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    public function __construct(private GradeAssignment $assignment)
    {
    }

    public function collection(): Collection
    {
        return $this->assignment->grades
            ->sortBy(fn ($grade) => strtolower($grade->student->name ?? ''))
            ->values()
            ->map(function ($grade, $index) {
                $maxScore = (float) $this->assignment->max_score;
                $score = $grade->score !== null ? (float) $grade->score : null;
                $percentage = $score !== null && $maxScore > 0 ? round(($score / $maxScore) * 100, 2) : null;
                $isLate = $score === null
                    && $this->assignment->due_date
                    && $this->assignment->due_date->lt(now()->startOfDay());

                return [
                    $index + 1,
                    $grade->student->nis ?? '-',
                    $grade->student->nisn ?? '-',
                    $grade->student->name ?? '-',
                    $this->assignment->class->name ?? '-',
                    $this->assignment->subject->name ?? '-',
                    $this->assignment->title,
                    $this->assignment->assigned_date?->format('d/m/Y') ?? '-',
                    $this->assignment->due_date?->format('d/m/Y') ?? '-',
                    $score !== null ? $score : '-',
                    $maxScore,
                    $percentage !== null ? $percentage . '%' : '-',
                    $isLate ? 'Terlambat' : ($score !== null ? 'Dinilai' : 'Belum dinilai'),
                    $grade->note ?: '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'NISN',
            'Nama Siswa',
            'Kelas',
            'Mata Pelajaran',
            'Nama Tugas',
            'Tanggal Tugas',
            'Batas Pengumpulan',
            'Nilai',
            'Nilai Maksimal',
            'Persentase',
            'Status',
            'Keterangan',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF059669']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 16,
            'C' => 18,
            'D' => 32,
            'E' => 16,
            'F' => 26,
            'G' => 30,
            'H' => 16,
            'I' => 18,
            'J' => 12,
            'K' => 15,
            'L' => 14,
            'M' => 18,
            'N' => 36,
        ];
    }

    public function title(): string
    {
        return 'Nilai Tugas';
    }
}
