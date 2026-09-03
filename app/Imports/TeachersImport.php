<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Subject;
use App\Models\Scopes\InstitutionScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;

class TeachersImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $headerFound = false;
        $cols = [];
        
        foreach ($rows as $rowIndex => $rowArray) {
            $row = $rowArray->toArray();
            
            if (!$headerFound) {
                $isHeader = false;
                foreach ($row as $i => $val) {
                    $v = strtolower(trim((string)$val));
                    if ($v === 'nama' || $v === 'nama guru') {
                        $cols['nama'] = $i;
                        $isHeader = true;
                    } elseif ($v === 'nip' || $v === 'nip / nuptk') {
                        $cols['nip'] = $i;
                    } elseif ($v === 'nik' || $v === 'nik/no ktp') {
                        $cols['nik'] = $i;
                    } elseif ($v === 'mengajar' || $v === 'mata pelajaran' || strpos($v, 'mapel') !== false) {
                        $cols['mengajar'] = $i;
                    } elseif ($v === 'telepon' || $v === 'no telepon' || strpos($v, 'telp') !== false || $v === 'hp') {
                        $cols['telepon'] = $i;
                    } elseif ($v === 'alamat') {
                        $cols['alamat'] = $i;
                    } elseif ($v === 'email') {
                        $cols['email'] = $i;
                    } elseif ($v === 'gelar depan') {
                        $cols['gelar_depan'] = $i;
                    } elseif ($v === 'gelar belakang') {
                        $cols['gelar_belakang'] = $i;
                    }
                }
                
                if ($isHeader) {
                    $headerFound = true;
                }
                continue; // Skip the header row itself
            }
            
            // Process data row
            $namaAsli = $row[$cols['nama'] ?? -1] ?? null;
            $nama = self::sanitizeFormulaPayload(trim((string)$namaAsli));
            
            // Jika nama kosong atau hanya angka (biasanya baris ke-5 dapodik yang isinya penomoran kolom)
            if (empty(trim($nama)) || is_numeric($nama) || strtolower(trim($nama)) === 'nama') {
                continue;
            }
            
            $nip = trim($row[$cols['nip'] ?? -1] ?? '');
            if (empty($nip)) {
                $nip = trim($row[$cols['nik'] ?? -1] ?? '');
            }
            
            if (empty($nip)) {
                \Log::info("Skipping Teacher '$nama': NIP and NIK both empty");
                continue; // Skip if no NIP/NIK
            }
            
            // Pada Dapodik, kolom mengajar biasanya di index 15 jika tidak ketemu judulnya karena merged cells
            $mengajarColIndex = $cols['mengajar'] ?? 15;
            $mengajar = $row[$mengajarColIndex] ?? null;
            
            if (empty(trim($mengajar ?? ''))) {
                \Log::info("Skipping Teacher '$nama': Mengajar empty");
                continue; // Sesuai request, abaikan kepegawaian yang tidak mengajar
            }
            
            $telepon = $row[$cols['telepon'] ?? -1] ?? null;
            $alamat = $row[$cols['alamat'] ?? -1] ?? null;
            $email = $row[$cols['email'] ?? -1] ?? null;
            
            $emailVal = !empty(trim($email)) ? trim($email) : ($nip . '@sekolah.sch.id');

            $gelarDepan = isset($cols['gelar_depan']) ? trim((string)($row[$cols['gelar_depan']] ?? '')) : '';
            $gelarBelakang = isset($cols['gelar_belakang']) ? trim((string)($row[$cols['gelar_belakang']] ?? '')) : '';
            
            $fullName = $nama;
            if (!empty($gelarDepan)) {
                $fullName = $gelarDepan . ' ' . $fullName;
            }
            if (!empty($gelarBelakang)) {
                $fullName = $fullName . ', ' . $gelarBelakang;
            }

            $institutionId = auth()->user()->institution_id;

            // Cari guru berdasarkan NIP dalam institusi yang sama (including soft-deleted)
            $teacher = User::withoutGlobalScope(InstitutionScope::class)
                ->withTrashed()
                ->where('nip', $nip)
                ->where('institution_id', $institutionId)
                ->first();

            if ($teacher) {
                // Restore jika sebelumnya dihapus
                if ($teacher->trashed()) {
                    $teacher->restore();
                }
                $teacher->update([
                    'name'     => $fullName,
                    'email'    => $emailVal,
                    'phone'    => $telepon,
                    'address'  => $alamat,
                ]);
            } else {
                $teacher = User::create([
                    'nip'      => $nip,
                    'name'     => $fullName,
                    'email'    => $emailVal,
                    'phone'    => $telepon,
                    'address'  => $alamat,
                    'password' => Hash::make('password123'),
                    'password_changed_at' => now(),
                    'email_verified_at' => now(),
                    'institution_id' => $institutionId,
                ]);
                $teacher->assignRole('teacher');
            }

            if (!empty($mengajar)) {
                $subjectNames = array_map('trim', explode(',', $mengajar));
                $subjectIds = [];
                
                foreach ($subjectNames as $subjectName) {
                    if (empty($subjectName)) continue;
                    
                    // Cari mapel, jika tidak ada, buat baru
                    $subject = Subject::firstOrCreate(
                        ['name' => $subjectName, 'institution_id' => $institutionId],
                        [
                            'code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $subjectName), 0, 5)) . '-' . rand(100, 999),
                            'credit_hours' => 2, // Default JP
                            'institution_id' => $institutionId,
                        ]
                    );
                    
                    $subjectIds[] = $subject->id;
                }
                
                $teacher->subjects()->syncWithoutDetaching($subjectIds);
            }
        }
    }

    private static function sanitizeFormulaPayload(string $value): string
    {
        if ($value === '' || $value[0] !== '=') {
            return $value;
        }
        return "'" . $value;
    }
}
