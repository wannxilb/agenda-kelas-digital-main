<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsDataExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $institutionId;
    protected $classId;

    public function __construct(?int $institutionId = null, $classId = null)
    {
        $this->institutionId = $institutionId;
        $this->classId = $classId;
    }

    public function collection()
    {
        $query = User::role('siswa')
            ->with('class:id,name,academic_year')
            ->when($this->institutionId, fn ($q) => $q->where('institution_id', $this->institutionId))
            ->when($this->classId, fn ($q) => $q->where('class_id', $this->classId))
            ->orderByRaw('LOWER(users.name) ASC');

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'NAMA',
            'NIS',
            'NISN',
            'GENDER',
            'KELAS',
            'STATUS',
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
        ];
    }

    public function map($student): array
    {
        return [
            $student->name,
            $student->nis,
            $student->nisn,
            $student->gender,
            $student->class?->name,
            $student->status,
            $student->tempat_lahir,
            $student->tanggal_lahir,
            $student->address,
            $student->rt,
            $student->rw,
            $student->kelurahan,
            $student->kecamatan,
            $student->phone,
            $student->parent_phone,
            $student->email,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
