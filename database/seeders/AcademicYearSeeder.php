<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\User;
use App\Models\Agenda;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\ClassHistory;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get active academic year or create default
        $activeYear = AcademicYear::where('institution_id', 1)
            ->where('is_active', true)
            ->first();

        if (!$activeYear) {
            $activeYear = AcademicYear::firstOrCreate(
                ['name' => '2025/2026', 'semester' => 'Ganjil', 'institution_id' => 1],
                [
                    'start_date' => '2025-07-01',
                    'end_date' => '2025-12-31',
                    'is_active' => true,
                ]
            );
        }

        // Link existing students (users with class_id) to ClassHistory
        $students = User::whereNotNull('class_id')->get();
        foreach ($students as $student) {
            ClassHistory::firstOrCreate([
                'user_id' => $student->id,
                'academic_year_id' => $activeYear->id,
            ], [
                'class_id' => $student->class_id,
            ]);
        }

        // Link existing Agendas to the active academic year
        Agenda::whereNull('academic_year_id')->update(['academic_year_id' => $activeYear->id]);

        // Link existing Schedules to the active academic year
        Schedule::whereNull('academic_year_id')->update(['academic_year_id' => $activeYear->id]);

        // Link existing Attendances to the active academic year
        Attendance::whereNull('academic_year_id')->update(['academic_year_id' => $activeYear->id]);
    }
}

