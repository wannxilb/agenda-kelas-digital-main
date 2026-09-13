<?php

namespace App\Imports;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;

class SubjectsImport implements ToCollection
{
    private int $importedCount = 0;
    private int $skippedCount = 0;

    public function collection(Collection $rows)
    {
        $institutionId = Auth::user()?->institution_id;
        $headerFound = false;
        $cols = [];

        foreach ($rows as $row) {
            $rowArray = $row->toArray();

            if (!$headerFound) {
                $isHeader = false;
                foreach ($rowArray as $i => $val) {
                    $v = strtolower(trim((string) $val));
                    if ($v === 'nama' || $v === 'nama mata pelajaran' || $v === 'nama mapel') {
                        $cols['name'] = $i;
                        $isHeader = true;
                    } elseif (in_array($v, ['jp', 'jam pelajaran', 'credit_hours', 'jam'])) {
                        $cols['credit_hours'] = $i;
                    } elseif (in_array($v, ['guru', 'guru pengampu', 'teacher', 'pengampu', 'nip guru'])) {
                        $cols['teacher'] = $i;
                    } elseif (in_array($v, ['deskripsi', 'keterangan', 'description'])) {
                        $cols['description'] = $i;
                    }
                }
                if ($isHeader) {
                    $headerFound = true;
                }
                continue;
            }

            $name = trim((string) ($rowArray[$cols['name'] ?? -1] ?? ''));
            if (empty($name)) {
                $this->skippedCount++;
                continue;
            }

            $creditHours = (int) ($rowArray[$cols['credit_hours'] ?? -1] ?? 2);
            if ($creditHours < 1 || $creditHours > 20) {
                $creditHours = 2;
            }

            $description = isset($cols['description']) ? trim((string) ($rowArray[$cols['description']] ?? '')) : null;

            $subject = Subject::firstOrCreate(
                ['name' => $name, 'institution_id' => $institutionId],
                [
                    'credit_hours' => $creditHours,
                    'description'  => $description,
                    'institution_id' => $institutionId,
                ]
            );

            if (!$subject->wasRecentlyCreated) {
                $subject->update([
                    'credit_hours' => $creditHours,
                    'description'  => $description ?: $subject->description,
                ]);
            }

            // Link to teacher if provided (by name or NIP)
            $teacherRaw = isset($cols['teacher']) ? trim((string) ($rowArray[$cols['teacher']] ?? '')) : '';
            if (!empty($teacherRaw)) {
                $teacher = User::role('teacher')
                    ->where('institution_id', $institutionId)
                    ->where(function ($q) use ($teacherRaw) {
                        $q->where('name', 'like', '%' . $teacherRaw . '%')
                          ->orWhere('nip', $teacherRaw);
                    })
                    ->first();

                if ($teacher) {
                    $subject->teachers()->syncWithoutDetaching([$teacher->id]);
                }
            }

            $this->importedCount++;
        }
    }

    public function getImportedCount(): int { return $this->importedCount; }
    public function getSkippedCount(): int { return $this->skippedCount; }
}
