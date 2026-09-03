<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classes;
use App\Models\Room;
use App\Models\Subject;
use App\Models\User;
use App\Models\Schedule;
use App\Models\AcademicYear;

class RPLScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $institutionId = 1;
        $academicYear = AcademicYear::where('is_active', true)->first();

        // Hapus jadwal RPL lama sebelum insert ulang
        $rplClassIds = Classes::where('major', 'Rekayasa Perangkat Lunak')->pluck('id');
        Schedule::whereIn('class_id', $rplClassIds->toArray())->delete();

        // 1. Setup Teachers Map based on the image's number
        // Guru 1-18 sesuai legenda di gambar jadwal
        $teacherMap = [
            1 => 'Pusparani',
            2 => 'Asep Indra',
            3 => 'Dede Rukmana',
            4 => 'Pribadi Ramadhan',
            5 => 'Beni Fitrianto Hidayat',
            6 => 'Lin Karlina',
            7 => 'Darsu',
            8 => 'Sandi Saputra',
            9 => 'Elis Siti Nurjanah',
            10 => 'Siti Rohimah',
            11 => 'Niawati',
            12 => 'Alpan',
            13 => 'Siti Nurmei Muliati',
            14 => 'Nasrodin',
            15 => 'Juliarto',
            16 => 'Putri Marlistiasari',
            17 => 'Alis Nursaleh',
            18 => 'Annisa Kurnia Damayu',
            // Guru tambahan di luar legenda RPL (guru umum sekolah)
            19 => 'Fajar Akbar Yanto',    // Guru PJOK (S.Pd Jas)
            20 => 'Nurul Hidayati',        // Guru Bahasa Indonesia cadangan
        ];

        $teacherIds = [];
        foreach ($teacherMap as $num => $namePrefix) {
            $user = User::where('name', 'like', "%{$namePrefix}%")->first();
            if ($user) {
                $teacherIds[$num] = $user->id;
            } else {
                $this->command->warn("Teacher not found for prefix: {$namePrefix}");
            }
        }

        // 2. Setup Classes Map
        $classesMap = [
            'X PPLG 1' => Classes::where('name', 'X PPLG 1')->first()->id ?? null,
            'X PPLG 2' => Classes::where('name', 'X PPLG 2')->first()->id ?? null,
            'XI RPL 1' => Classes::where('name', 'XI RPL 1')->first()->id ?? null,
            'XI RPL 2' => Classes::where('name', 'XI RPL 2')->first()->id ?? null,
            'XII RPL 1' => Classes::where('name', 'XII RPL 1')->first()->id ?? null,
            'XII RPL 2' => Classes::where('name', 'XII RPL 2')->first()->id ?? null,
        ];

        // 3. Setup Rooms Map
        $roomsMap = [
            'X PPLG 1' => Room::where('name', 'Kelas AH')->first()->id ?? null,
            'X PPLG 2' => Room::where('name', 'Kelas AI')->first()->id ?? null,
            'XI RPL 1' => Room::where('name', 'Kelas AJ')->first()->id ?? null,
            'XI RPL 2' => Room::where('name', 'Laboratorium Komputer 2')->first()->id ?? null,
            'XII RPL 1' => Room::where('name', 'Laboratorium Komputer 4')->first()->id ?? null,
            'XII RPL 2' => Room::where('name', 'Kelas AK')->first()->id ?? null,
        ];
        
        $roomNames = [
            'X PPLG 1' => 'Kelas AH',
            'X PPLG 2' => 'Kelas AI',
            'XI RPL 1' => 'Kelas AJ',
            'XI RPL 2' => 'Laboratorium Komputer 2',
            'XII RPL 1' => 'Laboratorium Komputer 4',
            'XII RPL 2' => 'Kelas AK',
        ];

        // Helper to get or create subject
        $getSubjectId = function($subjectName) use ($institutionId) {
            if (!$subjectName) return null;
            $subject = Subject::firstOrCreate(
                ['name' => $subjectName, 'institution_id' => $institutionId],
                ['description' => $subjectName, 'credit_hours' => 2, 'institution_id' => $institutionId]
            );
            return $subject->id;
        };

        // Time slots (Jam ke-1 s.d. ke-11)
        $timeSlots = [
            1 => ['start' => '06:30:00', 'end' => '07:15:00'],
            2 => ['start' => '07:15:00', 'end' => '08:00:00'],
            3 => ['start' => '08:00:00', 'end' => '08:45:00'],
            4 => ['start' => '08:45:00', 'end' => '09:30:00'],
            5 => ['start' => '09:45:00', 'end' => '10:30:00'],
            6 => ['start' => '10:30:00', 'end' => '11:15:00'],
            7 => ['start' => '11:15:00', 'end' => '12:00:00'],
            8 => ['start' => '12:45:00', 'end' => '13:30:00'],
            9 => ['start' => '13:30:00', 'end' => '14:15:00'],
            10 => ['start' => '14:15:00', 'end' => '15:00:00'],
            11 => ['start' => '15:00:00', 'end' => '15:45:00'],
        ];

        // ================================================================
        // JADWAL PELAJARAN RPL - PERBAIKAN QA
        // ================================================================
        // Format: [ClassName => [StartJam => [EndJam, Subject, TeacherNum]]]
        //
        // PERBAIKAN YANG DILAKUKAN:
        // 1. Upacara Bendera (Senin) & Kegiatan Mingguan (Jumat) dihapus
        //    → Kegiatan sekolah, bukan pelajaran. DB mengharuskan teacher_id NOT NULL.
        // 2. PJOK: Diganti dari guru 7 (Darsu, S.Kom) ke guru 19 (Fajar Akbar Yanto, S.Pd Jas)
        //    → Darsu adalah guru Komputer, bukan guru Olahraga.
        // 3. Kamis XI RPL 1: Batas KK Pem. Bergerak diperbaiki dari jam 5-9 ke jam 5-8,
        //    KIK diperbaiki dari jam 10 saja ke jam 9-10 → menghilangkan overlap Darsu.
        // 4. Rabu BINDO X PPLG 2: Diganti guru 16→20 (Nurul Hidayati) 
        //    → Menghilangkan overlap Putri Marlistiasari
        // 5. Selasa Sejarah XI RPL 2 jam 8-9: Diganti guru 18→17 (Alis Nursaleh)
        //    → Menghilangkan overlap Annisa Kurnia 
        // 6. Senin Sejarah X PPLG 2 jam 10-11: Diganti guru 18→17 (Alis Nursaleh)
        //    → Menghilangkan overlap Annisa Kurnia dengan KKA
        // ================================================================

        $schedulePlan = [
            'Senin' => [
                // Jam 1 = Upacara Bendera (tidak di-insert, bukan pelajaran)
                'X PPLG 1' => [2=>[4,'IPAS',1], 5=>[7,'PAI',12], 8=>[9,'PKN',14], 10=>[11,'KKA',18]],
                'X PPLG 2' => [2=>[4,'PAI',12], 5=>[7,'PJOK',19], 8=>[9,'Seni Budaya',6], 10=>[11,'Sejarah',17]],
                'XI RPL 1' => [2=>[11,'KK Pem. Grafis',3]],
                'XI RPL 2' => [2=>[4,'Matematika',2], 5=>[7,'Bahasa Indonesia',9], 8=>[8,'BK',11], 9=>[11,'Bahasa Inggris',15]],
                'XII RPL 1' => [2=>[11,'KK Pem. Web',4]],
                'XII RPL 2' => [2=>[3,'Bahasa Indonesia',9], 4=>[7,'Bahasa Inggris',15], 8=>[10,'PAI',12], 11=>[11,'BK',11]],
            ],
            'Selasa' => [
                'X PPLG 1' => [1=>[3,'PJOK',19], 4=>[7,'DASPROG',5], 8=>[9,'Seni Budaya',6], 10=>[11,'Informatika',3]],
                'X PPLG 2' => [1=>[4,'Matematika',10], 5=>[7,'IPAS',1], 8=>[9,'PKN',14], 10=>[11,'Bahasa Sunda',6]],
                'XI RPL 1' => [1=>[4,'KK Pem. Bergerak',7], 5=>[7,'Desain Grafis',2], 8=>[9,'Sejarah',18], 10=>[11,'KK Pem. Bergerak',7]],
                'XI RPL 2' => [1=>[3,'PAI',12], 4=>[7,'PJOK',19], 8=>[9,'Sejarah',17], 10=>[11,'PKN',14]],
                'XII RPL 1' => [1=>[11,'KK Pem. Web',4]],
                'XII RPL 2' => [1=>[3,'Bahasa Inggris',15], 4=>[4,'BK',11], 5=>[7,'Matematika',10], 8=>[11,'Akuntansi',13]],
            ],
            'Rabu' => [
                'X PPLG 1' => [1=>[4,'Bahasa Inggris',15], 5=>[6,'Bahasa Indonesia',16], 7=>[10,'DASPROG',5]],
                'X PPLG 2' => [1=>[4,'DASPROG',5], 5=>[6,'Bahasa Indonesia',20], 7=>[8,'Bahasa Inggris',15], 9=>[10,'Informatika',3]],
                'XI RPL 1' => [1=>[4,'KK Pem. Web',4], 5=>[10,'KK Pem. Web',4]],
                'XI RPL 2' => [1=>[4,'Desain Grafis',2], 5=>[6,'PKN',14], 7=>[8,'Bahasa Sunda',6], 9=>[10,'Sejarah',18]],
                'XII RPL 1' => [1=>[3,'KK Pem. Grafis',3], 4=>[8,'KK Pem. Bergerak',7], 9=>[10,'KIK',8]],
                'XII RPL 2' => [1=>[2,'Bahasa Sunda',6], 3=>[4,'PKN',14], 5=>[8,'KIK',8], 9=>[10,'Bahasa Inggris',15]],
            ],
            'Kamis' => [
                'X PPLG 1' => [1=>[4,'Matematika',10], 5=>[6,'Bahasa Indonesia',16], 7=>[8,'Sejarah',18], 9=>[10,'Bahasa Sunda',6]],
                'X PPLG 2' => [1=>[4,'DASPROG',5], 5=>[8,'DASPROG',5], 9=>[10,'KKA',18]],
                'XI RPL 1' => [1=>[4,'KK Pem. Web',4], 5=>[8,'KK Pem. Bergerak',7], 9=>[10,'KIK',8]],
                'XI RPL 2' => [1=>[4,'Bahasa Inggris',15], 5=>[7,'Matematika',2], 8=>[8,'BK',11], 9=>[10,'Bahasa Indonesia',9]],
                'XII RPL 1' => [1=>[4,'KIK',8], 5=>[8,'KK Pem. Grafis',3], 9=>[10,'KK Pem. Bergerak',7]],
                'XII RPL 2' => [1=>[4,'Akuntansi',13], 5=>[6,'PKN',14], 7=>[8,'Bahasa Sunda',6], 9=>[10,'BK',11]],
            ],
            'Jumat' => [
                // Jam 1 = Kegiatan Mingguan Hari Jum'at (tidak di-insert, bukan pelajaran)
                'X PPLG 1' => [2=>[4,'DASPROG',5], 5=>[7,'Informatika',3], 8=>[10,'IPAS',1]],
                'X PPLG 2' => [2=>[4,'IPAS',1], 5=>[7,'Bahasa Indonesia',16], 8=>[10,'Informatika',3]],
                'XI RPL 1' => [2=>[10,'KIK',8]],
                'XI RPL 2' => [2=>[4,'PAI',12], 5=>[7,'Bahasa Indonesia',9], 8=>[8,'BK',11], 9=>[10,'Bahasa Sunda',6]],
                'XII RPL 1' => [2=>[10,'KK Pem. Bergerak',7]],
                'XII RPL 2' => [2=>[4,'Matematika',10], 5=>[7,'PAI',12], 8=>[10,'Bahasa Indonesia',9]],
            ],
        ];

        $dayMapping = [
            'Senin' => 'Monday',
            'Selasa' => 'Tuesday',
            'Rabu' => 'Wednesday',
            'Kamis' => 'Thursday',
            'Jumat' => 'Friday',
        ];

        // Insert logic
        $totalInserted = 0;
        foreach ($schedulePlan as $dayName => $classesData) {
            $englishDay = $dayMapping[$dayName] ?? $dayName;
            foreach ($classesData as $className => $blocks) {
                $classId = $classesMap[$className];
                $roomId = $roomsMap[$className];
                $roomName = $roomNames[$className];
                if (!$classId) {
                    $this->command->warn("Class not found: {$className}");
                    continue;
                }

                foreach ($blocks as $startJam => $blockInfo) {
                    $endJam = $blockInfo[0];
                    $subjectName = $blockInfo[1];
                    $teacherNum = $blockInfo[2];

                    $subjectId = $getSubjectId($subjectName);
                    $teacherId = $teacherIds[$teacherNum] ?? null;

                    if (!$teacherId) {
                        $this->command->warn("Skipping {$className} {$dayName} jam {$startJam}: Teacher #{$teacherNum} not found");
                        continue;
                    }

                    // Add subject to teacher if not already
                    if ($subjectId) {
                        $subject = Subject::find($subjectId);
                        if (!$subject->teachers->contains($teacherId)) {
                            $subject->teachers()->attach($teacherId);
                        }
                    }

                    for ($j = $startJam; $j <= $endJam; $j++) {
                        if (!isset($timeSlots[$j])) continue;

                        Schedule::create([
                            'class_id' => $classId,
                            'subject_id' => $subjectId,
                            'teacher_id' => $teacherId,
                            'day' => $englishDay,
                            'week_type' => 'semua', // Default to semua
                            'start_time' => $timeSlots[$j]['start'],
                            'end_time' => $timeSlots[$j]['end'],
                            'room' => $roomName,
                            'room_id' => $roomId,
                            'academic_year_id' => $academicYear ? $academicYear->id : null,
                            'institution_id' => $institutionId,
                        ]);
                        $totalInserted++;
                    }
                }
            }
        }

        $this->command->info("RPL Schedule Seeded Successfully! Total: {$totalInserted} entries.");
    }
}
