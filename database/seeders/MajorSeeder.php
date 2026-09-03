<?php

namespace Database\Seeders;

use App\Models\Major;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MajorSeeder extends Seeder
{
    public function run(): void
    {
        $institutionId = 1;

        $majors = [
            [
                'name' => 'Rekayasa Perangkat Lunak',
                'code' => 'RPL',
                'grades' => [
                    'X'   => ['code' => 'PPLG', 'rombel_count' => 2],
                    'XI'  => ['code' => 'RPL',  'rombel_count' => 2],
                    'XII' => ['code' => 'RPL',  'rombel_count' => 2],
                ],
            ],
            [
                'name' => 'Akuntansi',
                'code' => 'AK',
                'grades' => [
                    'X'   => ['code' => 'AKL', 'rombel_count' => 2],
                    'XI'  => ['code' => 'AK',  'rombel_count' => 2],
                    'XII' => ['code' => 'AK',  'rombel_count' => 2],
                ],
            ],
            [
                'name' => 'Bisnis Digital',
                'code' => 'BD',
                'grades' => [
                    'X'   => ['code' => 'PM', 'rombel_count' => 2],
                    'XI'  => ['code' => 'BD', 'rombel_count' => 3],
                    'XII' => ['code' => 'BD', 'rombel_count' => 3],
                ],
            ],
            [
                'name' => 'Desain Komunikasi Visual',
                'code' => 'DKV',
                'grades' => [
                    'X'   => ['code' => 'DKV', 'rombel_count' => 2],
                    'XI'  => ['code' => 'DKV', 'rombel_count' => 2],
                    'XII' => ['code' => 'DKV', 'rombel_count' => 2],
                ],
            ],
            [
                'name' => 'Desain dan Produksi Busana',
                'code' => 'DPB',
                'grades' => [
                    'X'   => ['code' => 'BS',  'rombel_count' => 2],
                    'XI'  => ['code' => 'DPB', 'rombel_count' => 2],
                    'XII' => ['code' => 'DPB', 'rombel_count' => 2],
                ],
            ],
            [
                'name' => 'Manajemen Perkantoran',
                'code' => 'MP',
                'grades' => [
                    'X'   => ['code' => 'MPLB', 'rombel_count' => 3],
                    'XI'  => ['code' => 'MP',  'rombel_count' => 3],
                    'XII' => ['code' => 'MP',  'rombel_count' => 3],
                ],
            ],
            [
                'name' => 'Perfilman',
                'code' => 'PF',
                'grades' => [
                    'X'   => ['code' => 'BCF', 'rombel_count' => 1],
                    'XI'  => ['code' => 'PF',  'rombel_count' => 1],
                    'XII' => ['code' => 'PF',  'rombel_count' => 1],
                ],
            ],
        ];

        foreach ($majors as $majorData) {
            $grades = $majorData['grades'];
            unset($majorData['grades']);

            $major = Major::firstOrCreate(
                ['institution_id' => $institutionId, 'name' => $majorData['name']],
                ['code' => $majorData['code'], 'is_active' => true]
            );

            foreach ($grades as $gradeLevel => $template) {
                DB::table('major_grade_templates')->updateOrInsert(
                    ['major_id' => $major->id, 'grade_level' => $gradeLevel],
                    [
                        'code' => $template['code'],
                        'rombel_count' => $template['rombel_count'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('Master jurusan dan konfigurasi rombel berhasil dibuat.');
    }
}
