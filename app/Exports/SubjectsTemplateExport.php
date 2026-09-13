<?php

namespace App\Exports;

use App\Models\Subject;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;

class SubjectsTemplateExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithMapping, WithStyles
{
    public function collection()
    {
        $institutionId = Auth::user()?->institution_id;
        return Subject::where('institution_id', $institutionId)
            ->with('teachers')
            ->get();
    }

    public function map($subject): array
    {
        $teacher = $subject->teachers->first();
        return [
            $subject->name,
            $subject->credit_hours,
            $teacher ? $teacher->name : '',
            $subject->description ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Mata Pelajaran',
            'JP (Jam Pelajaran)',
            'Guru Pengampu',
            'Deskripsi',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '059669']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Mata Pelajaran';
    }
}
