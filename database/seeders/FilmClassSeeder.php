<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\Classes;

class FilmClassSeeder extends Seeder
{
    public function run(): void
    {
        $institutionId = 1;

        $tahunAjaran = [
            'X'   => ['year' => '2025/2026', 'start' => '2025-07-01', 'end' => '2026-06-30'],
            'XI'  => ['year' => '2024/2025', 'start' => '2024-07-01', 'end' => '2025-06-30'],
            'XII' => ['year' => '2023/2024', 'start' => '2023-07-01', 'end' => '2024-06-30'],
        ];

        $kelas = [
            ['grade' => 'X',   'prefix' => 'BCF', 'qty' => 1],
            ['grade' => 'XI',  'prefix' => 'PF',  'qty' => 1],
            ['grade' => 'XII', 'prefix' => 'PF',  'qty' => 1],
        ];

        foreach ($kelas as $k) {
            $ta = $tahunAjaran[$k['grade']];

            AcademicYear::firstOrCreate(
                ['name' => $ta['year']],
                [
                    'start_date' => $ta['start'],
                    'end_date' => $ta['end'],
                    'is_active' => $k['grade'] === 'X',
                ]
            );

            for ($i = 1; $i <= $k['qty']; $i++) {
                $namaKelas = $k['qty'] > 1 ? "{$k['grade']} {$k['prefix']} {$i}" : "{$k['grade']} {$k['prefix']}";

                Classes::firstOrCreate(
                    ['name' => $namaKelas, 'institution_id' => $institutionId],
                    [
                        'major' => 'Perfilman',
                        'grade_level' => $k['grade'],
                        'academic_year' => $ta['year'],
                        'capacity' => 36,
                        'is_active' => true,
                        'institution_id' => $institutionId,
                    ]
                );
            }
        }

        $this->command->info('Kelas jurusan Perfilman berhasil dibuat.');
    }
}
