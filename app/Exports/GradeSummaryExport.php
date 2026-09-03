<?php

namespace App\Exports;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Subject;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradeSummaryExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    public function __construct(
        private Classes $class,
        private Subject $subject,
        private ?AcademicYear $academicYear,
        private Collection $students,
        private Collection $assignments,
        private Collection $gradeMatrix
    ) {
    }

    public function collection(): Collection
    {
        return $this->students->values()->map(function ($student, $index) {
            $row = [
                $index + 1,
                $student->nis ?? '-',
                $student->nisn ?? '-',
                $student->name,
            ];

            $scores = [];
            foreach ($this->assignments as $assignment) {
                $score = $this->gradeMatrix->get($student->id . ':' . $assignment->id)?->score;
                $isLate = $score === null
                    && $assignment->due_date
                    && $assignment->due_date->lt(now()->startOfDay());

                $row[] = $score !== null ? (float) $score : ($isLate ? 'Terlambat' : '-');
                if ($score !== null) {
                    $scores[] = (float) $score;
                }
            }

            $row[] = count($scores) ? round(array_sum($scores) / count($scores), 2) : '-';
            $row[] = count($scores);

            return $row;
        });
    }

    public function headings(): array
    {
        $assignmentHeadings = $this->assignments
            ->map(fn ($assignment) => $assignment->title . ' (' . ($assignment->assigned_date?->format('d/m') ?? '-') . ($assignment->due_date ? ', batas ' . $assignment->due_date->format('d/m') : '') . ')')
            ->all();

        return array_merge(
            ['No', 'NIS', 'NISN', 'Nama Siswa'],
            $assignmentHeadings,
            ['Rata-rata', 'Jumlah Nilai Terisi']
        );
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF047857']],
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
        ];
    }

    public function title(): string
    {
        return 'Rekap Nilai';
    }
}
