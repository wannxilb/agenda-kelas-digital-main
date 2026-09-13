<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class TeachersTemplateExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithMapping
{
    public function collection()
    {
        return User::role('teacher')->get();
    }

    public function map($teacher): array
    {
        return [
            $teacher->name,
            $teacher->nip,
            $teacher->phone,
            $teacher->address,
            $teacher->email,
            '', // Gelar Depan (tidak disimpan terpisah di DB)
            '', // Gelar Belakang (tidak disimpan terpisah di DB)
            $teacher->subjects->pluck('name')->implode(', '),
        ];
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NIP',
            'Telepon',
            'Alamat',
            'Email',
            'Gelar Depan',
            'Gelar Belakang',
            'Mengajar'
        ];
    }

    public function title(): string
    {
        return 'Data Guru';
    }
}
