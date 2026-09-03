<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Agenda;
use App\Models\Attendance;
use App\Models\StudentDailyAttendance;
use App\Models\StudentEarlyLeaveRequest;
use App\Models\GradeAssignment;
use App\Models\StudentGrade;
use App\Models\TeacherStatus;
use App\Models\Setting;
use App\Models\DailyAttendanceSetting;
use App\Models\AuditLog;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FakeDataSeeder extends Seeder
{
    private int $institutionId = 1;
    private array $indonesianMaleFirst = [
        'Ahmad', 'Muhammad', 'Rizki', 'Fajar', 'Dimas', 'Reza', 'Aditya', 'Bayu',
        'Fadil', 'Ilham', 'Rafi', 'Gilang', 'Farhan', 'Yusuf', 'Alif', 'Nabil',
        'Arif', 'Raka', 'Dwiki', 'Kenzy', 'Aryo', 'Bintang', 'Cakra', 'Elang',
        'Ghifari', 'Hafiz', 'Irfan', 'Jordi', 'Kafi', 'Luthfi', 'Malik', 'Nathan',
        'Omar', 'Putra', 'Raihan', 'Satria', 'Taufik', 'Umar', 'Vino', 'Wafi',
        'Yusuf', 'Zaki', 'Azka', 'Bravo', 'Cristo', 'Darma', 'Eka', 'Ferdi',
        'Ganda', 'Hadi', 'Indra', 'Julian', 'Kunto', 'Lestar', 'Mitra', 'Nanda',
        'Oktav', 'Pras', 'Rudi', 'Surya', 'Teguh', 'Ucup', 'Vikri', 'Wahyu',
    ];

    private array $indonesianFemaleFirst = [
        'Siti', 'Aisyah', 'Nisa', 'Putri', 'Dina', 'Rina', 'Wati', 'Sari',
        'Ayuning', 'Bening', 'Citra', 'Dewi', 'Eka', 'Fitri', 'Gita', 'Hana',
        'Indah', 'Jihan', 'Kartika', 'Laila', 'Mila', 'Nadia', 'Olivia', 'Putri',
        'Qanita', 'Ratna', 'Salsa', 'Tara', 'Ulya', 'Vera', 'Wulan', 'Yolanda',
        'Zahra', 'Alya', 'Bunga', 'Cinthya', 'Dian', 'Elin', 'Flora', 'Gisel',
        'Halimah', 'Ika', 'Julia', 'Kania', 'Lintang', 'Mawar', 'Nabila', 'Oktavia',
        'Prama', 'Ririn', 'Sinta', 'Tasya', 'Utami', 'Vina', 'Winda', 'Yunita',
    ];

    private array $lastNames = [
        'Pratama', 'Putra', 'Putri', 'Saputra', 'Setiawan', 'Wijaya', 'Gunawan',
        'Santoso', 'Hidayat', 'Kurniawan', 'Susanto', 'Pramono', 'Hermawan',
        'Rahman', 'Firmansyah', 'Suharto', 'Wibowo', 'Utomo', 'Sugiyarto', 'Basuki',
    ];

    private array $subjects = [
        ['name' => 'Matematika', 'hours' => 4],
        ['name' => 'Bahasa Indonesia', 'hours' => 4],
        ['name' => 'Bahasa Inggris', 'hours' => 3],
        ['name' => 'Pendidikan Agama Islam', 'hours' => 2],
        ['name' => 'Pendidikan Pancasila', 'hours' => 2],
        ['name' => 'Sejarah Indonesia', 'hours' => 2],
        ['name' => 'Seni Budaya', 'hours' => 2],
        ['name' => 'Penjaskes', 'hours' => 2],
        ['name' => 'Informatika', 'hours' => 3],
        ['name' => 'Fisika', 'hours' => 3],
        ['name' => 'Kimia', 'hours' => 3],
        ['name' => 'Biologi', 'hours' => 3],
        ['name' => 'Ekonomi', 'hours' => 2],
        ['name' => 'Geografi', 'hours' => 2],
        ['name' => 'Sosiologi', 'hours' => 2],
    ];

    private array $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

    public function run(): void
    {
        $this->command->info('Memulai pembuatan data palsu...');

        $this->seedSettings();
        $this->seedDailyAttendanceSetting();

        $teachers = $this->getTeachers();
        $classes = Classes::where('institution_id', $this->institutionId)
            ->where('is_active', true)
            ->get();
        $subjects = $this->seedSubjects();
        $academicYear = AcademicYear::where('is_active', true)->first();

        $this->assignHomeroomTeachers($classes, $teachers);
        $students = $this->seedStudents($classes);
        $this->seedSchedules($classes, $teachers, $subjects, $academicYear);
        $this->seedAgendas($classes, $teachers, $subjects, $academicYear);
        $this->seedDailyAttendances($students, $classes, $academicYear);
        $this->seedEarlyLeaveRequests($students, $classes, $teachers, $academicYear);
        $this->seedGradeAssignments($classes, $teachers, $subjects, $students, $academicYear);
        $this->seedTeacherStatuses($teachers, $academicYear);
        $this->seedAuditLogs();

        $this->command->info('Data palsu berhasil dibuat!');
        $this->printSummary();
    }

    private function getTeachers(): Collection
    {
        return User::where('institution_id', $this->institutionId)
            ->where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
            ->get();
    }

    private function seedSettings(): void
    {
        $settings = [
            'school_name' => 'SMK Digital Nusantara',
            'school_address' => 'Jl. Pendidikan No. 123, Kota Bandung',
            'academic_year' => '2025/2026',
            'semester' => 'Ganjil',
            'operational_start_time' => '06:30',
            'operational_end_time' => '16:00',
            'operational_days' => 'Senin,Selasa,Rabu,Kamis,Jumat',
            'daily_check_in_start' => '05:00',
            'daily_check_in_deadline' => '07:30',
            'daily_check_in_verification_deadline' => '08:00',
            'daily_check_out_start' => '14:30',
            'daily_check_out_tolerance_minutes' => '15',
            'daily_late_tolerance_minutes' => '15',
            'daily_require_check_in_photo' => '1',
            'daily_require_check_out_photo' => '1',
            'daily_whatsapp_enabled' => '0',
            'daily_whatsapp_provider' => 'fonnte',
            'daily_whatsapp_api_url' => 'https://api.fonnte.com/send',
            'daily_whatsapp_country_code' => '62',
            'daily_check_in_message_template' => 'Ananda {student} telah masuk sekolah pukul {time}. Status: {status}.',
            'daily_check_out_message_template' => 'Ananda {student} telah pulang sekolah pukul {time}. Status: {status}.',
            'feature_agenda_harian' => '1',
            'feature_attendance' => '1',
            'feature_schedule' => '1',
            'feature_rooms' => '1',
            'feature_export_pdf' => '1',
            'feature_export_excel' => '1',
            'feature_import_data' => '1',
            'feature_nilai_tugas' => '1',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value, 'general', $this->institutionId);
        }

        $this->command->info('  Settings berhasil dibuat.');
    }

    private function seedDailyAttendanceSetting(): void
    {
        DailyAttendanceSetting::forInstitution($this->institutionId);
        $this->command->info('  Daily Attendance Setting berhasil dibuat.');
    }

    private function assignHomeroomTeachers(Collection $classes, Collection $teachers): void
    {
        $teacherIndex = 0;
        foreach ($classes as $class) {
            if ($class->homeroom_teacher_id) {
                continue;
            }
            $teacher = $teachers[$teacherIndex % $teachers->count()];
            $class->update(['homeroom_teacher_id' => $teacher->id]);
            if (!$teacher->hasRole('wali_kelas')) {
                $teacher->assignRole('wali_kelas');
            }
            $teacherIndex++;
        }
        $this->command->info('  Wali kelas ditugaskan ke ' . $classes->count() . ' kelas.');
    }

    private function seedStudents(Collection $classes): Collection
    {
        $allStudents = collect();
        $usedEmails = [];
        $nisCounter = 10001;
        $nisnCounter = 2601001;

        foreach ($classes as $class) {
            $studentCount = rand(28, 36);
            for ($i = 0; $i < $studentCount; $i++) {
                $gender = $this->randomGender();
                $firstName = $gender === 'L'
                    ? $this->indonesianMaleFirst[array_rand($this->indonesianMaleFirst)]
                    : $this->indonesianFemaleFirst[array_rand($this->indonesianFemaleFirst)];
                $lastName = $this->lastNames[array_rand($this->lastNames)];
                $name = $firstName . ' ' . $lastName;

                $baseEmail = Str::slug($firstName . '.' . $lastName) . '@student.school.com';
                $email = $baseEmail;
                $counter = 1;
                while (in_array($email, $usedEmails)) {
                    $email = Str::slug($firstName . '.' . $lastName) . $counter . '@student.school.com';
                    $counter++;
                }
                $usedEmails[] = $email;

                $nis = (string) $nisCounter++;
                $nisn = (string) $nisnCounter++;

                $student = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'institution_id' => $this->institutionId,
                    'class_id' => $class->id,
                    'status' => 'active',
                    'gender' => $gender,
                    'nis' => $nis,
                    'nisn' => $nisn,
                    'phone' => $this->randomPhone(),
                    'parent_phone' => $this->randomPhone(),
                    'address' => fake()->address(),
                    'tempat_lahir' => fake()->city(),
                    'tanggal_lahir' => fake()->dateTimeBetween('-16 years', '-14 years')->format('Y-m-d'),
                    'rt' => (string) rand(1, 20),
                    'rw' => (string) rand(1, 10),
                    'kelurahan' => fake()->words(1, true),
                    'kecamatan' => fake()->words(1, true),
                ]);

                $student->assignRole('siswa');
                $allStudents->push($student);
            }
        }

        $this->command->info('  ' . $allStudents->count() . ' siswa dibuat di ' . $classes->count() . ' kelas.');
        return $allStudents;
    }

    private function seedSubjects(): Collection
    {
        $created = collect();
        foreach ($this->subjects as $sub) {
            $subject = Subject::firstOrCreate(
                ['name' => $sub['name'], 'institution_id' => $this->institutionId],
                [
                    'description' => 'Mata pelajaran ' . $sub['name'],
                    'credit_hours' => $sub['hours'],
                    'institution_id' => $this->institutionId,
                ]
            );
            $created->push($subject);
        }
        $this->command->info('  ' . $created->count() . ' mata pelajaran dibuat.');
        return $created;
    }

    private function seedSchedules(
        Collection $classes,
        Collection $teachers,
        Collection $subjects,
        ?AcademicYear $academicYear
    ): void {
        $count = 0;
        foreach ($classes as $class) {
            $classSubjects = $subjects->random(min(6, $subjects->count()));
            $timeSlots = [
                ['start' => '07:30', 'end' => '09:00'],
                ['start' => '09:00', 'end' => '10:30'],
                ['start' => '10:45', 'end' => '12:15'],
                ['start' => '13:00', 'end' => '14:30'],
                ['start' => '14:30', 'end' => '16:00'],
            ];

            $dayIndex = 0;
            $slotIndex = 0;
            foreach ($classSubjects as $subject) {
                $day = $this->days[$dayIndex % count($this->days)];
                $slot = $timeSlots[$slotIndex % count($timeSlots)];
                $teacher = $teachers->random();

                Schedule::create([
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher->id,
                    'day' => $day,
                    'start_time' => $slot['start'] . ':00',
                    'end_time' => $slot['end'] . ':00',
                    'room' => $class->name,
                    'academic_year_id' => $academicYear?->id,
                    'institution_id' => $this->institutionId,
                ]);

                $subject->teachers()->syncWithoutDetaching([$teacher->id]);

                $slotIndex++;
                if ($slotIndex >= count($timeSlots)) {
                    $slotIndex = 0;
                    $dayIndex++;
                }
                $count++;
            }
        }
        $this->command->info('  ' . $count . ' jadwal pelajaran dibuat.');
    }

    private function seedAgendas(
        Collection $classes,
        Collection $teachers,
        Collection $subjects,
        ?AcademicYear $academicYear
    ): void {
        $count = 0;
        $today = Carbon::today();

        for ($dayOffset = 0; $dayOffset < 5; $dayOffset++) {
            $date = $today->copy()->subDays($dayOffset);
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($classes->random(min(8, $classes->count())) as $class) {
                $schedules = Schedule::where('class_id', $class->id)
                    ->where('day', $date->translatedFormat('l'))
                    ->get();

                foreach ($schedules as $schedule) {
                    if (rand(1, 100) > 80) {
                        continue;
                    }

                    Agenda::create([
                        'class_id' => $class->id,
                        'teacher_id' => $schedule->teacher_id,
                        'subject_id' => $schedule->subject_id,
                        'schedule_id' => $schedule->id,
                        'room' => $schedule->room,
                        'date' => $date->format('Y-m-d'),
                        'title' => $this->randomAgendaTitle($schedule->subject),
                        'description' => fake()->sentence(rand(5, 15)),
                        'status' => rand(1, 100) > 20 ? 'published' : 'draft',
                        'academic_year_id' => $academicYear?->id,
                        'institution_id' => $this->institutionId,
                    ]);
                    $count++;
                }
            }
        }
        $this->command->info('  ' . $count . ' agenda mengajar dibuat.');
    }

    private function seedDailyAttendances(
        Collection $students,
        Collection $classes,
        ?AcademicYear $academicYear
    ): void {
        $count = 0;
        $today = Carbon::today();
        $statuses = ['present', 'present', 'present', 'present', 'late', 'late', 'alpha', 'sick', 'excused'];

        for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
            $date = $today->copy()->subDays($dayOffset);
            if ($date->isWeekend()) {
                continue;
            }

            $dayStudents = $students->random(min(40, $students->count()));

            foreach ($dayStudents as $student) {
                $status = $statuses[array_rand($statuses)];

                $checkInTime = null;
                $checkOutTime = null;
                $checkInStatus = null;
                $lateMinutes = 0;

                if ($status === 'present' || $status === 'late') {
                    if ($status === 'late') {
                        $lateMinutes = rand(5, 45);
                        $checkInTime = $date->copy()->addHours(7)->addMinutes(30 + $lateMinutes);
                        $checkInStatus = 'late';
                    } else {
                        $checkInTime = $date->copy()->addHours(rand(6, 7))->addMinutes(rand(0, 59));
                        $checkInStatus = 'teacher_verified';
                    }

                    $checkOutTime = $date->copy()->addHours(14)->addMinutes(rand(30, 59));
                } elseif ($status === 'alpha') {
                    $checkInStatus = 'alpha';
                } elseif ($status === 'sick') {
                    $checkInStatus = 'sick';
                } elseif ($status === 'excused') {
                    $checkInStatus = 'excused';
                }

                $lat = -6.9175 + (rand(-100, 100) / 10000);
                $lng = 107.6191 + (rand(-100, 100) / 10000);

                StudentDailyAttendance::create([
                    'student_id' => $student->id,
                    'class_id' => $student->class_id,
                    'date' => $date->format('Y-m-d'),
                    'check_in_at' => $checkInTime?->format('Y-m-d H:i:s'),
                    'check_in_latitude' => $checkInTime ? $lat : null,
                    'check_in_longitude' => $checkInTime ? $lng : null,
                    'check_in_accuracy' => $checkInTime ? rand(5, 30) : null,
                    'check_in_ip_address' => $checkInTime ? fake()->ipv4() : null,
                    'check_in_user_agent' => $checkInTime ? 'Mozilla/5.0 (Linux; Android 12)' : null,
                    'check_in_suspicious' => false,
                    'check_in_status' => $checkInStatus,
                    'late_minutes' => $lateMinutes,
                    'check_out_at' => $checkOutTime?->format('Y-m-d H:i:s'),
                    'check_out_latitude' => $checkOutTime ? $lat : null,
                    'check_out_longitude' => $checkOutTime ? $lng : null,
                    'check_out_accuracy' => $checkOutTime ? rand(5, 30) : null,
                    'check_out_ip_address' => $checkOutTime ? fake()->ipv4() : null,
                    'check_out_status' => $checkOutTime ? 'checked_out' : null,
                    'academic_year_id' => $academicYear?->id,
                    'institution_id' => $this->institutionId,
                ]);
                $count++;
            }
        }
        $this->command->info('  ' . $count . ' catatan presensi harian dibuat.');
    }

    private function seedEarlyLeaveRequests(
        Collection $students,
        Collection $classes,
        Collection $teachers,
        ?AcademicYear $academicYear
    ): void {
        $count = 0;
        $today = Carbon::today();
        $categories = ['sakit', 'izin', 'izin_lainnya', 'lomba', 'kegiatan_sekolah', 'kegiatan_tambahan', 'pulang_sakit', 'pulang_awal'];
        $statuses = ['pending', 'approved', 'approved', 'approved', 'rejected'];
        $reasons = [
            'Sakit flu dan demam tinggi',
            'Keperluan keluarga mendadak',
            'Mengikuti lomba programming tingkat kota',
            'Kegiatan ekstrakurikuler pramuka',
            'Sakit perut sejak pagi',
            'Ada acara keluarga di luar kota',
            'Mewakili sekolah dalam kompetisi robotik',
            'Keperluan urusan orang tua',
        ];

        for ($i = 0; $i < 60; $i++) {
            $student = $students->random();
            $category = $categories[array_rand($categories)];
            $status = $statuses[array_rand($statuses)];
            $date = $today->copy()->subDays(rand(0, 10));
            if ($date->isWeekend()) {
                $date = $date->subDay();
            }

            $isMultiDay = in_array($category, ['sakit', 'izin', 'izin_lainnya', 'lomba', 'kegiatan_sekolah']) && rand(1, 100) > 60;
            $dateEnd = $isMultiDay ? $date->copy()->addDays(rand(1, 3)) : null;

            $reviewedBy = $status !== 'pending' ? $teachers->random()->id : null;
            $reviewedAt = $status !== 'pending' ? $date->copy()->addHours(rand(1, 6))->format('Y-m-d H:i:s') : null;

            StudentEarlyLeaveRequest::create([
                'student_id' => $student->id,
                'class_id' => $student->class_id,
                'date' => $date->format('Y-m-d'),
                'date_end' => $dateEnd?->format('Y-m-d'),
                'category' => $category,
                'reason' => $reasons[array_rand($reasons)],
                'evidence_path' => 'evidence/fake_' . Str::random(10) . '.jpg',
                'evidence_mime' => 'image/jpeg',
                'status' => $status,
                'reviewed_by' => $reviewedBy,
                'reviewed_at' => $reviewedAt,
                'reviewer_note' => $status === 'approved' ? 'Disetujui' : ($status === 'rejected' ? 'Tidak dapat disetujui' : null),
                'subject_teacher_name' => in_array($category, ['lomba', 'kegiatan_sekolah']) ? $teachers->random()->name : null,
                'activity_name' => in_array($category, ['lomba', 'kegiatan_sekolah'])
                    ? ['Lomba Web Development', 'Osis Meeting', 'Persiapan Pentas Seni', 'Training Robotik'][array_rand([0, 1, 2, 3])]
                    : null,
                'institution_id' => $this->institutionId,
            ]);
            $count++;
        }
        $this->command->info('  ' . $count . ' pengajuan izin/sakit dibuat.');
    }

    private function seedGradeAssignments(
        Collection $classes,
        Collection $teachers,
        Collection $subjects,
        Collection $students,
        ?AcademicYear $academicYear
    ): void {
        $assignmentCount = 0;
        $gradeCount = 0;
        $titles = ['Tugas 1', 'Tugas 2', 'UTS', 'UAS', 'Quiz 1', 'Quiz 2', 'Praktikum', 'Proyek Akhir'];
        $today = Carbon::today();

        foreach ($classes->random(min(6, $classes->count())) as $class) {
            $classSubjects = $subjects->random(min(4, $subjects->count()));
            $classStudents = $students->filter(fn ($s) => $s->class_id === $class->id);

            foreach ($classSubjects as $subject) {
                $teacher = $teachers->random();
                $assignment = GradeAssignment::create([
                    'teacher_id' => $teacher->id,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'title' => $titles[array_rand($titles)] . ' - ' . $subject->name,
                    'description' => 'Penilaian ' . $subject->name . ' kelas ' . $class->name,
                    'assigned_date' => $today->copy()->subDays(rand(5, 30))->format('Y-m-d'),
                    'due_date' => $today->copy()->addDays(rand(1, 14))->format('Y-m-d'),
                    'max_score' => 100,
                    'academic_year_id' => $academicYear?->id,
                    'institution_id' => $this->institutionId,
                ]);
                $assignmentCount++;

                foreach ($classStudents as $student) {
                    if (rand(1, 100) <= 15) {
                        continue;
                    }

                    StudentGrade::create([
                        'grade_assignment_id' => $assignment->id,
                        'student_id' => $student->id,
                        'score' => rand(40, 100) . '.' . rand(0, 99),
                        'note' => rand(1, 100) > 80 ? 'Perlu perbaikan' : null,
                        'institution_id' => $this->institutionId,
                    ]);
                    $gradeCount++;
                }
            }
        }
        $this->command->info('  ' . $assignmentCount . ' tugas penilaian dan ' . $gradeCount . ' nilai siswa dibuat.');
    }

    private function seedTeacherStatuses(
        Collection $teachers,
        ?AcademicYear $academicYear
    ): void {
        $count = 0;
        $today = Carbon::today();
        $types = ['izin', 'sakit', 'tugas_luar'];
        $statuses = ['pending', 'approved', 'approved', 'rejected'];
        $notes = [
            'Izin pribadi',
            'Sakit demam',
            'Tugas dinas ke dinas pendidikan',
            'Izin urusan keluarga',
            'Sakit kepala',
            'Menghadiri workshop di luar kota',
        ];

        $selectedTeachers = $teachers->random(min(10, $teachers->count()));

        foreach ($selectedTeachers as $teacher) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $type = $types[array_rand($types)];
                $status = $statuses[array_rand($statuses)];
                $date = $today->copy()->subDays(rand(0, 14));
                if ($date->isWeekend()) {
                    $date = $date->subDay();
                }

                TeacherStatus::create([
                    'teacher_id' => $teacher->id,
                    'type' => $type,
                    'status' => $status,
                    'date' => $date->format('Y-m-d'),
                    'date_end' => rand(1, 100) > 70 ? $date->copy()->addDays(rand(1, 2))->format('Y-m-d') : null,
                    'start_time' => $type === 'tugas_luar' ? '08:00' : null,
                    'end_time' => $type === 'tugas_luar' ? '12:00' : null,
                    'note' => $notes[array_rand($notes)],
                    'academic_year_id' => $academicYear?->id,
                    'institution_id' => $this->institutionId,
                ]);
                $count++;
            }
        }
        $this->command->info('  ' . $count . ' status guru (izin/sakit/tugas luar) dibuat.');
    }

    private function seedAuditLogs(): void
    {
        $admin = User::where('institution_id', $this->institutionId)
            ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->first();

        if (!$admin) {
            return;
        }

        $actions = ['create', 'update', 'delete'];
        $models = ['User', 'Classes', 'Subject', 'Schedule', 'StudentEarlyLeaveRequest'];
        $count = 0;

        for ($i = 0; $i < 30; $i++) {
            AuditLog::create([
                'user_id' => $admin->id,
                'action' => $actions[array_rand($actions)],
                'model_type' => 'App\\Models\\' . $models[array_rand($models)],
                'model_id' => rand(1, 100),
                'old_values' => null,
                'new_values' => ['name' => fake()->word()],
                'ip_address' => fake()->ipv4(),
                'user_agent' => 'Mozilla/5.0',
                'institution_id' => $this->institutionId,
            ]);
            $count++;
        }
        $this->command->info('  ' . $count . ' log audit dibuat.');
    }

    private function randomGender(): string
    {
        return rand(1, 100) > 45 ? 'L' : 'P';
    }

    private function randomPhone(): string
    {
        $prefixes = ['0812', '0813', '0815', '0816', '0817', '0818', '0819', '0821', '0822', '0823', '0851', '0852', '0853'];
        return $prefixes[array_rand($prefixes)] . rand(10000000, 99999999);
    }

    private function randomAgendaTitle(Subject $subject): string
    {
        $templates = [
            'Pembelajaran {subject} - Materi Baru',
            'Praktikum {subject}',
            'Review dan Diskusi {subject}',
            'Presentasi Kelompok {subject}',
            'Ulangan Harian {subject}',
            'Penugasan {subject}',
        ];
        $template = $templates[array_rand($templates)];
        return str_replace('{subject}', $subject->name, $template);
    }

    private function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('=== RINGKASAN DATA PALSU ===');
        $this->command->info('Siswa        : ' . User::where('institution_id', $this->institutionId)->whereHas('roles', fn ($q) => $q->where('name', 'siswa'))->count());
        $this->command->info('Guru         : ' . User::where('institution_id', $this->institutionId)->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))->count());
        $this->command->info('Kelas        : ' . Classes::where('institution_id', $this->institutionId)->count());
        $this->command->info('Mata Pelajaran: ' . Subject::where('institution_id', $this->institutionId)->count());
        $this->command->info('Jadwal       : ' . Schedule::where('institution_id', $this->institutionId)->count());
        $this->command->info('Agenda       : ' . Agenda::where('institution_id', $this->institutionId)->count());
        $this->command->info('Presensi     : ' . StudentDailyAttendance::where('institution_id', $this->institutionId)->count());
        $this->command->info('Izin/Sakit   : ' . StudentEarlyLeaveRequest::where('institution_id', $this->institutionId)->count());
        $this->command->info('Tugas Nilai  : ' . GradeAssignment::where('institution_id', $this->institutionId)->count());
        $this->command->info('Nilai Siswa  : ' . StudentGrade::where('institution_id', $this->institutionId)->count());
        $this->command->info('Status Guru  : ' . TeacherStatus::where('institution_id', $this->institutionId)->count());
        $this->command->info('');
        $this->command->info('Akun Login:');
        $this->command->info('  Admin    : admin@school.com / password');
        $this->command->info('  Guru     : guru@school.com / password');
        $this->command->info('  Siswa    : (email siswa pertama) / password');
        $this->command->info('');
    }
}
