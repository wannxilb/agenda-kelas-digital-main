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
            $teacher->email,
            $teacher->nip,
            $teacher->phone,
            $teacher->address,
            '', // Password blank for template/security
        ];
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Email',
            'NIP',
            'Telepon',
            'Alamat',
            'Password'
        ];
    }

    public function title(): string
    {
        return 'Data Guru';
    }
}
