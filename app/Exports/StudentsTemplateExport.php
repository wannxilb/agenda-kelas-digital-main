<?php
// app/Exports/StudentsTemplateExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use App\Models\Classes;

class StudentsTemplateExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        // Ambil nama kelas yang nyata dan aktif di database sebagai contoh agar tidak terjadi error pencocokan kelas
        $sampleClass = Classes::where('is_active', true)->first() ?? Classes::first();
        $sampleClassName = $sampleClass ? $sampleClass->name : 'X RPL 1';

        return collect([
            [
                'Ahmad Wijaya',
                '2024001',
                'L',
                '0098765432',
                'Bandung',
                '2008-05-15',
                'Jl. Merdeka No. 10',
                '03',
                '05',
                'Babakan Ciamis',
                'Sumur Bandung',
                '081234567891',
                '081298765432',
                'siswa@school.com',
                $sampleClassName
            ],
        ]);
    }
    
    public function headings(): array
    {
        return [
            'NAMA',
            'NIS',
            'GENDER',
            'NISN',
            'TEMPAT LAHIR',
            'TANGGAL LAHIR',
            'ALAMAT',
            'RT',
            'RW',
            'KELURAHAN',
            'KECAMATAN',
            'NO TELEPON',
            'NO WA ORANG TUA',
            'EMAIL',
            'KELAS'
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}