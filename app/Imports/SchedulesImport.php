<?php

namespace App\Imports;

use App\Models\Classes;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use App\Models\AcademicYear;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class SchedulesImport implements ToCollection
{
    private int $importedCount = 0;
    private int $skippedCount = 0;
    private array $errors = [];

    private array $dayMap = [
        'senin'  => 'Monday',
        'selasa' => 'Tuesday',
        'rabu'   => 'Wednesday',
        'kamis'  => 'Thursday',
        'jumat'  => 'Friday',
        "jum'at" => 'Friday',
        'jumat'  => 'Friday',
        'monday'    => 'Monday',
        'tuesday'   => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday'  => 'Thursday',
        'friday'    => 'Friday',
    ];

    private array $weekTypeMap = [
        'semua'  => 'semua',
        'all'    => 'semua',
        'tiap'   => 'semua',
        'ganjil' => 'ganjil',
        'genap'  => 'genap',
        'odd'    => 'ganjil',
        'even'   => 'genap',
        '1'      => 'ganjil',
        '2'      => 'genap',
    ];

    public function collection(Collection $rows)
    {
        $institutionId = Auth::user()?->institution_id;
        $headerFound = false;
        $cols = [];

        $academicYear = AcademicYear::where('institution_id', $institutionId)
            ->where('is_active', true)
            ->first();

        foreach ($rows as $row) {
            $rowArray = $row->toArray();

            if (!$headerFound) {
                $isHeader = false;
                foreach ($rowArray as $i => $val) {
                    $v = strtolower(trim((string) $val));
                    if (in_array($v, ['kelas', 'nama kelas', 'class'])) {
                        $cols['class'] = $i;
                        $isHeader = true;
                    } elseif (in_array($v, ['mata pelajaran', 'mapel', 'pelajaran', 'subject'])) {
                        $cols['subject'] = $i;
                    } elseif (in_array($v, ['guru', 'guru pengampu', 'teacher', 'nip guru', 'pengampu'])) {
                        $cols['teacher'] = $i;
                    } elseif (in_array($v, ['hari', 'day'])) {
                        $cols['day'] = $i;
                    } elseif (in_array($v, ['tipe minggu', 'minggu', 'week_type', 'tipe', 'jenis minggu'])) {
                        $cols['week_type'] = $i;
                    } elseif (in_array($v, ['jam mulai', 'mulai', 'start', 'start_time', 'dari'])) {
                        $cols['start_time'] = $i;
                    } elseif (in_array($v, ['jam selesai', 'selesai', 'end', 'end_time', 'sampai', 'hingga'])) {
                        $cols['end_time'] = $i;
                    } elseif (in_array($v, ['ruangan', 'ruang', 'room', 'kode ruang'])) {
                        $cols['room'] = $i;
                    }
                }
                if ($isHeader) {
                    $headerFound = true;
                }
                continue;
            }

            // --- Read row values ---
            $className   = trim((string) ($rowArray[$cols['class']   ?? -1] ?? ''));
            $subjectName = trim((string) ($rowArray[$cols['subject'] ?? -1] ?? ''));
            $teacherRaw  = trim((string) ($rowArray[$cols['teacher'] ?? -1] ?? ''));
            $dayRaw      = trim((string) ($rowArray[$cols['day']     ?? -1] ?? ''));
            $startRaw    = trim((string) ($rowArray[$cols['start_time'] ?? -1] ?? ''));
            $endRaw      = trim((string) ($rowArray[$cols['end_time']   ?? -1] ?? ''));
            $weekTypeRaw = trim((string) ($rowArray[$cols['week_type']  ?? -1] ?? 'semua'));
            $roomRaw     = trim((string) ($rowArray[$cols['room']       ?? -1] ?? ''));

            if (empty($className) || empty($subjectName) || empty($dayRaw) || empty($startRaw) || empty($endRaw)) {
                $this->skippedCount++;
                continue;
            }

            // --- Resolve day ---
            $dayEnglish = $this->dayMap[strtolower($dayRaw)] ?? null;
            if (!$dayEnglish) {
                $this->errors[] = "Hari tidak dikenali: '{$dayRaw}' (baris dengan kelas {$className})";
                $this->skippedCount++;
                continue;
            }

            // --- Normalize time ---
            $startTime = $this->parseTime($startRaw);
            $endTime   = $this->parseTime($endRaw);
            if (!$startTime || !$endTime) {
                $this->errors[] = "Format jam tidak valid: '{$startRaw}' - '{$endRaw}' (kelas {$className}, {$dayRaw})";
                $this->skippedCount++;
                continue;
            }

            // --- Resolve week_type ---
            $weekType = $this->weekTypeMap[strtolower($weekTypeRaw)] ?? 'semua';

            // --- Find Class ---
            $class = Classes::where('institution_id', $institutionId)
                ->where(function ($q) use ($className) {
                    $q->where('name', 'like', '%' . $className . '%')
                      ->orWhere('name', $className);
                })->first();
            if (!$class) {
                $this->errors[] = "Kelas tidak ditemukan: '{$className}'";
                $this->skippedCount++;
                continue;
            }

            // --- Find Subject ---
            $subject = Subject::where('institution_id', $institutionId)
                ->where(function ($q) use ($subjectName) {
                    $q->where('name', 'like', '%' . $subjectName . '%')
                      ->orWhere('name', $subjectName);
                })->first();
            if (!$subject) {
                $this->errors[] = "Mata pelajaran tidak ditemukan: '{$subjectName}'";
                $this->skippedCount++;
                continue;
            }

            // --- Find Teacher ---
            $teacher = null;
            if (!empty($teacherRaw)) {
                $teacher = User::role('teacher')
                    ->where('institution_id', $institutionId)
                    ->where(function ($q) use ($teacherRaw) {
                        $q->where('name', 'like', '%' . $teacherRaw . '%')
                          ->orWhere('nip', $teacherRaw);
                    })->first();
            }
            if (!$teacher) {
                // Try first teacher of subject
                $teacher = $subject->teachers()->first();
            }
            if (!$teacher) {
                $this->errors[] = "Guru tidak ditemukan untuk: '{$subjectName}' (kelas {$className}, {$dayRaw})";
                $this->skippedCount++;
                continue;
            }

            // --- Find Room (optional) ---
            $roomId   = null;
            $roomName = $roomRaw;
            if (!empty($roomRaw)) {
                $room = Room::where('institution_id', $institutionId)
                    ->where(function ($q) use ($roomRaw) {
                        $q->where('name', 'like', '%' . $roomRaw . '%')
                          ->orWhere('code', $roomRaw);
                    })->first();
                if ($room) {
                    $roomId   = $room->id;
                    $roomName = $room->name;
                }
            }

            // --- Check for duplicates ---
            $exists = Schedule::withoutGlobalScopes()
                ->where('institution_id', $institutionId)
                ->where('class_id', $class->id)
                ->where('subject_id', $subject->id)
                ->where('teacher_id', $teacher->id)
                ->where('day', $dayEnglish)
                ->where('week_type', $weekType)
                ->where('start_time', $startTime)
                ->where('end_time', $endTime)
                ->exists();

            if ($exists) {
                $this->skippedCount++;
                continue;
            }

            Schedule::create([
                'class_id'       => $class->id,
                'subject_id'     => $subject->id,
                'teacher_id'     => $teacher->id,
                'day'            => $dayEnglish,
                'week_type'      => $weekType,
                'start_time'     => $startTime,
                'end_time'       => $endTime,
                'room'           => $roomName,
                'room_id'        => $roomId,
                'academic_year_id' => $academicYear?->id,
                'institution_id' => $institutionId,
            ]);

            $this->importedCount++;
        }
    }

    private function parseTime(string $raw): ?string
    {
        $raw = trim($raw);
        // Handle HH:MM or HH:MM:SS
        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $raw, $m)) {
            return sprintf('%02d:%02d:00', $m[1], $m[2]);
        }
        // Handle Excel decimal time (e.g., 0.291667 = 07:00)
        if (is_numeric($raw)) {
            $seconds = (float) $raw * 86400;
            $h = floor($seconds / 3600);
            $m = floor(($seconds % 3600) / 60);
            return sprintf('%02d:%02d:00', $h, $m);
        }
        return null;
    }

    public function getImportedCount(): int { return $this->importedCount; }
    public function getSkippedCount(): int  { return $this->skippedCount; }
    public function getErrors(): array      { return $this->errors; }
}
