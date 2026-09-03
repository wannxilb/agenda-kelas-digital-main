<?php
// app/Imports/StudentsImport.php

namespace App\Imports;

use App\Models\User;
use App\Models\Classes;
use App\Models\Scopes\InstitutionScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection
{
    protected $classId;
    protected $institutionId;
    protected $importedCount = 0;
    protected $skippedCount = 0;

    public function __construct($classId = null, ?int $institutionId = null)
    {
        $this->classId = $classId;
        $this->institutionId = $institutionId ?? auth()->user()?->institution_id;
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }

    protected function getMatchingClass($kelasName)
    {
        if (empty($kelasName)) {
            return null;
        }

        $className = trim($kelasName);
        $instId = $this->institutionId;

        // 1. Try exact match first (prioritize active class)
        $class = Classes::where('name', $className)
            ->when($instId, fn($q) => $q->where('institution_id', $instId))
            ->where('is_active', true)->first();
        if ($class) {
            return $class;
        }

        $class = Classes::where('name', $className)
            ->when($instId, fn($q) => $q->where('institution_id', $instId))
            ->first();
        if ($class) {
            return $class;
        }

        // 2. Try Roman/Number normalization match
        // Map of numeric-to-roman conversions
        $romanMap = [
            '10' => 'X',
            '11' => 'XI',
            '12' => 'XII',
        ];
        
        // Try to replace starting digits (e.g. "11 RPL 1" -> "XI RPL 1")
        foreach ($romanMap as $num => $roman) {
            if (preg_match('/^' . $num . '\s+/i', $className)) {
                $convertedName = preg_replace('/^' . $num . '\s+/i', $roman . ' ', $className);
                $class = Classes::where('name', $convertedName)->when($instId, fn($q) => $q->where('institution_id', $instId))->where('is_active', true)->first()
                      ?? Classes::where('name', $convertedName)->when($instId, fn($q) => $q->where('institution_id', $instId))->first();
                if ($class) {
                    return $class;
                }
            }

            // Try replacing without spaces
            if (strpos($className, $num) === 0) {
                $convertedName = preg_replace('/^' . $num . '/i', $roman, $className);
                $class = Classes::where('name', $convertedName)->when($instId, fn($q) => $q->where('institution_id', $instId))->where('is_active', true)->first()
                      ?? Classes::where('name', $convertedName)->when($instId, fn($q) => $q->where('institution_id', $instId))->first();
                if ($class) {
                    return $class;
                }
            }
        }

        // Try the reverse (e.g. "XI RPL 1" -> "11 RPL 1")
        foreach ($romanMap as $num => $roman) {
            if (strpos($className, $roman . ' ') === 0) {
                $convertedName = preg_replace('/^' . $roman . '\s+/', $num . ' ', $className);
                $class = Classes::where('name', $convertedName)->when($instId, fn($q) => $q->where('institution_id', $instId))->where('is_active', true)->first()
                      ?? Classes::where('name', $convertedName)->when($instId, fn($q) => $q->where('institution_id', $instId))->first();
                if ($class) {
                    return $class;
                }
            }

            if (strpos($className, $roman) === 0) {
                $convertedName = preg_replace('/^' . $roman . '/', $num, $className);
                $class = Classes::where('name', $convertedName)->when($instId, fn($q) => $q->where('institution_id', $instId))->where('is_active', true)->first()
                      ?? Classes::where('name', $convertedName)->when($instId, fn($q) => $q->where('institution_id', $instId))->first();
                if ($class) {
                    return $class;
                }
            }
        }

        return null;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception("File Excel tidak memiliki data untuk diimpor.");
        }

        // Optimasi: Hash password default cukup sekali saja di luar perulangan (mempercepat proses import hingga 100x)
        $defaultPasswordHash = Hash::make('password');

        $firstRow = $rows->first()->toArray();
        $isStandardTemplate = false;
        
        $namaColIndex = null;
        $nisColIndex = null;
        $genderColIndex = null;
        $nisnColIndex = null;
        $tempatLahirColIndex = null;
        $tanggalLahirColIndex = null;
        $alamatColIndex = null;
        $rtColIndex = null;
        $rwColIndex = null;
        $kelurahanColIndex = null;
        $kecamatanColIndex = null;
        $phoneColIndex = null;
        $parentPhoneColIndex = null;
        $emailColIndex = null;
        $kelasColIndex = null;
        
        // Cek baris pertama untuk mencocokkan header kolom
        foreach ($firstRow as $index => $value) {
            if ($value === null) continue;
            
            $val = strtolower(trim($value));
            // Hapus karakter non-printable, BOM UTF-8, dsb.
            $val = preg_replace('/[\x00-\x1F\x7F-\x9F\xEF\xBB\xBF]/', '', $val);
            
            if ($val === 'nama' || $val === 'nama siswa' || $val === '1') {
                $namaColIndex = $index;
            } elseif ($val === 'nis' || $val === '2') {
                $nisColIndex = $index;
            } elseif ($val === 'nisn' || $val === '4') {
                $nisnColIndex = $index;
            } elseif ($val === 'kelas' || $val === '42') {
                $kelasColIndex = $index;
            } elseif ($val === 'gender' || $val === 'jenis kelamin' || $val === 'l/p' || $val === 'jenis_kelamin' || $val === '3') {
                $genderColIndex = $index;
            } elseif ($val === 'alamat' || $val === '9') {
                $alamatColIndex = $index;
            } elseif ($val === 'tempat lahir' || $val === 'tempat_lahir' || $val === '5') {
                $tempatLahirColIndex = $index;
            } elseif ($val === 'tanggal lahir' || $val === 'tanggal_lahir' || $val === '6') {
                $tanggalLahirColIndex = $index;
            } elseif ($val === 'rt' || $val === '10') {
                $rtColIndex = $index;
            } elseif ($val === 'rw' || $val === '11') {
                $rwColIndex = $index;
            } elseif ($val === 'kelurahan' || $val === '13') {
                $kelurahanColIndex = $index;
            } elseif ($val === 'kecamatan' || $val === '14') {
                $kecamatanColIndex = $index;
            } elseif (in_array($val, ['no telepon', 'no_telepon', 'telepon', 'phone', 'no telp', 'no. telp', '19'])) {
                $phoneColIndex = $index;
            } elseif (in_array($val, ['no wa orang tua', 'no_wa_orang_tua', 'no whatsapp orang tua', 'nomor wa orang tua', 'parent phone', 'parent_phone', 'no ortu', 'no. ortu', 'wa orang tua', 'whatsapp orang tua', 'no telp ortu'])) {
                $parentPhoneColIndex = $index;
            } elseif ($val === 'email' || $val === '20') {
                $emailColIndex = $index;
            }
        }
        
        // Dianggap standard template jika minimal memiliki kolom NAMA dan NIS
        if ($namaColIndex !== null && $nisColIndex !== null) {
            $isStandardTemplate = true;
        }

        // Jika bukan standard template (tanpa header seperti file user), wajib memilih kelas tujuan di modal
        if (!$isStandardTemplate && !$this->classId) {
            throw new \Exception("Format file Excel Anda tidak memiliki judul kolom standar (NAMA, NIS, KELAS). Silakan pilih 'Kelas Tujuan' pada form import terlebih dahulu.");
        }

        foreach ($rows as $rowIndex => $row) {
            $rowArray = $row->toArray();
            
            // Skip baris pertama (jika terdeteksi sebagai header)
            if ($rowIndex === 0) {
                // Di template kustom atau template standar, kita lewati baris ke-1 jika memiliki header
                if ($isStandardTemplate || (isset($rowArray[0]) && strtolower(trim($rowArray[0])) === 'no')) {
                    continue;
                }
            }

            if ($isStandardTemplate) {
                $nama = $rowArray[$namaColIndex] ?? null;
                $nis = $rowArray[$nisColIndex] ?? null;
                $gender = $rowArray[$genderColIndex] ?? null;
                $kelasName = $kelasColIndex !== null ? ($rowArray[$kelasColIndex] ?? null) : null;
                $alamat = $alamatColIndex !== null ? ($rowArray[$alamatColIndex] ?? null) : null;
                
                $nisn = $nisnColIndex !== null ? ($rowArray[$nisnColIndex] ?? null) : null;
                $tempat_lahir = $tempatLahirColIndex !== null ? ($rowArray[$tempatLahirColIndex] ?? null) : null;
                $tanggal_lahir_raw = $tanggalLahirColIndex !== null ? ($rowArray[$tanggalLahirColIndex] ?? null) : null;
                $rt = $rtColIndex !== null ? ($rowArray[$rtColIndex] ?? null) : null;
                $rw = $rwColIndex !== null ? ($rowArray[$rwColIndex] ?? null) : null;
                $kelurahan = $kelurahanColIndex !== null ? ($rowArray[$kelurahanColIndex] ?? null) : null;
                $kecamatan = $kecamatanColIndex !== null ? ($rowArray[$kecamatanColIndex] ?? null) : null;
                $phone = $phoneColIndex !== null ? ($rowArray[$phoneColIndex] ?? null) : null;
                $parentPhone = $parentPhoneColIndex !== null ? ($rowArray[$parentPhoneColIndex] ?? null) : null;
                $email = $emailColIndex !== null ? ($rowArray[$emailColIndex] ?? null) : null;
            } else {
                // Menggunakan indeks posisi (Excel Kustom User)
                // Kolom B (index 1) = NAMA
                // Kolom C (index 2) = NIS
                // Kolom D (index 3) = GENDER
                $nama = $rowArray[1] ?? null;
                $nis = $rowArray[2] ?? null;
                $gender = $rowArray[3] ?? null;
                
                // Cek jika jumlah kolom besar, kemungkinan ini format Dapodik tetapi tanpa baris header yang dikenali
                if (count($rowArray) >= 15) {
                    $nisn = $rowArray[4] ?? null;
                    $tempat_lahir = $rowArray[5] ?? null;
                    $tanggal_lahir_raw = $rowArray[6] ?? null;
                    $alamat = $rowArray[7] ?? null;
                    $rt = $rowArray[8] ?? null;
                    $rw = $rowArray[9] ?? null;
                    $kelurahan = $rowArray[10] ?? null;
                    $kecamatan = $rowArray[11] ?? null;
                    $phone = $rowArray[12] ?? null;
                    $email = $rowArray[13] ?? null;
                    $kelasName = $rowArray[14] ?? null;
                    $parentPhone = null;
                } else {
                    $kelasName = null;
                    $alamat = $rowArray[7] ?? null;
                    
                    $nisn = null;
                    $tempat_lahir = null;
                    $tanggal_lahir_raw = null;
                    $rt = null;
                    $rw = null;
                    $kelurahan = null;
                    $kecamatan = null;
                    $phone = null;
                    $email = null;
                    $parentPhone = null;
                }
            }

            // Bersihkan data string
            $nama = $nama !== null ? self::sanitizeFormulaPayload(trim($nama)) : null;
            
            $nis = self::cleanNumericField($nis);

            $nisn = self::cleanNumericField($nisn);

            $tempat_lahir = $tempat_lahir !== null ? trim($tempat_lahir) : null;
            if ($tempat_lahir === '-' || empty($tempat_lahir)) {
                $tempat_lahir = null;
            }
            
            $rt = self::cleanNumericField($rt);
            if ($rt !== null && $rt !== '0') {
                $rt = str_pad($rt, 2, '0', STR_PAD_LEFT);
            } else {
                $rt = null;
            }

            $rw = self::cleanNumericField($rw);
            if ($rw !== null && $rw !== '0') {
                $rw = str_pad($rw, 2, '0', STR_PAD_LEFT);
            } else {
                $rw = null;
            }
            
            $kelurahan = $kelurahan !== null ? trim($kelurahan) : null;
            if ($kelurahan === '-' || empty($kelurahan)) {
                $kelurahan = null;
            }
            
            $kecamatan = $kecamatan !== null ? trim($kecamatan) : null;
            if ($kecamatan === '-' || empty($kecamatan)) {
                $kecamatan = null;
            }
            
            $phone = self::cleanNumericField($phone);
            if ($phone === '-' || empty($phone)) {
                $phone = null;
            }

            $parentPhone = self::cleanNumericField($parentPhone);
            if ($parentPhone === '-' || empty($parentPhone)) {
                $parentPhone = null;
            }

            $emailVal = !empty($email) ? trim($email) : ($nis . '@agenda.local');
            
            // Perbaiki: Pastikan email unik per institusi (termasuk yang soft-deleted)
            if (User::withoutGlobalScope(InstitutionScope::class)->withTrashed()->where('email', $emailVal)->exists()) {
                $emailVal = $nis . '_' . uniqid() . '@agenda.local';
            }

            // Parse tanggal lahir secara fleksibel
            $tanggal_lahir = null;
            if (!empty($tanggal_lahir_raw)) {
                $tanggal_lahir_raw = trim($tanggal_lahir_raw);
                if (is_numeric($tanggal_lahir_raw)) {
                    try {
                        $tanggal_lahir = date('Y-m-d', ($tanggal_lahir_raw - 25569) * 86400);
                    } catch (\Exception $e) {
                        $tanggal_lahir = null;
                    }
                } else {
                    $timestamp = strtotime(str_replace('/', '-', $tanggal_lahir_raw));
                    if ($timestamp !== false) {
                        $tanggal_lahir = date('Y-m-d', $timestamp);
                    }
                }
            }

            // Skip baris kosong (ghost rows dari Excel)
            if (empty($nama) && empty($nis)) {
                continue;
            }

            // Jika salah satu dari Nama atau NIS kosong, skip baris ini
            if (empty($nama) || empty($nis)) {
                $this->skippedCount++;
                continue;
            }

            // Skip jika NIS sudah terdaftar di institusi yang sama (termasuk yang soft-deleted)
            if (User::withoutGlobalScope(InstitutionScope::class)->withTrashed()
                    ->where('nis', $nis)
                    ->where('institution_id', auth()->user()->institution_id)
                    ->exists()) {
                $this->skippedCount++;
                continue;
            }

            // Skip jika NISN sudah terdaftar di institusi yang sama (termasuk yang soft-deleted)
            if (!empty($nisn) && User::withoutGlobalScope(InstitutionScope::class)->withTrashed()
                    ->where('nisn', $nisn)
                    ->where('institution_id', auth()->user()->institution_id)
                    ->exists()) {
                $this->skippedCount++;
                continue;
            }

            // Tentukan ID kelas (Memprioritaskan Kelas Tujuan dari dropdown jika dipilih)
            $targetClassId = null;
            $classFromExcel = null;

            if (!empty($kelasName)) {
                $classFromExcel = $this->getMatchingClass($kelasName);
            }

            if (!empty($this->classId)) {
                // Dropdown dipilih: hanya import baris yang kelasnya cocok
                if (!$classFromExcel) {
                    $this->skippedCount++;
                    continue;
                }
                if ($classFromExcel->id != $this->classId) {
                    $this->skippedCount++;
                    continue;
                }
                $targetClassId = $this->classId;
            } elseif ($classFromExcel) {
                // Dropdown not selected, use Excel class
                $targetClassId = $classFromExcel->id;
            } else {
                // Neither dropdown nor excel class found
                $this->skippedCount++;
                continue;
            }

            // Bersihkan data gender (null bila tidak terdeteksi tegas)
            $genderVal = self::normalizeGender($gender);

            $student = User::create([
                'name' => $nama,
                'email' => $emailVal,
                'nis' => $nis,
                'gender' => $genderVal,
                'class_id' => $targetClassId,
                'phone' => $phone,
                'parent_phone' => $parentPhone,
                'address' => $alamat !== null ? trim($alamat) : null,
                'password' => $defaultPasswordHash,
                'password_changed_at' => now(),
                'email_verified_at' => now(),
                'nisn' => $nisn,
                'tempat_lahir' => $tempat_lahir,
                'tanggal_lahir' => $tanggal_lahir,
                'rt' => $rt,
                'rw' => $rw,
                'kelurahan' => $kelurahan,
                'kecamatan' => $kecamatan,
                'institution_id' => auth()->user()->institution_id,
            ]);
            
            $student->assignRole('siswa');
            $this->importedCount++;
        }
    }

    private static function sanitizeFormulaPayload(string $value): string
    {
        if ($value === '' || $value[0] !== '=') {
            return $value;
        }
        return "'" . $value;
    }

    /**
     * Bersihkan nilai numerik dari Excel: trim, buang akhiran desimal palsu
     * (mis. "2024001.00" -> "2024001", "03.0" -> "03"), koma / spasi, dan
     * baris bertanda "-".
     */
    private static function cleanNumericField($value)
    {
        if ($value === null) {
            return null;
        }
        $val = trim((string) $value);
        if ($val === '' || $val === '-') {
            return null;
        }
        // Hapus akhiran desimal .0 / .00 / ,0 (artefak Excel)
        if (preg_match('/^(\d+)\.0+$/', $val, $m)) {
            return ltrim($m[1], '0');
        }
        // Koma desimal gaya Eropa mis. "2024001,00"
        $val = str_replace([' ', ','], '', $val);
        return $val;
    }

    private static function normalizeGender($gender)
    {
        $g = strtoupper(trim((string) $gender));
        if ($g === 'P' || $g === 'PEREMPUAN' || $g === 'F' || $g === 'FEMALE') {
            return 'P';
        }
        if ($g === 'L' || $g === 'LAKI-LAKI' || $g === 'M' || $g === 'MALE' || $g === 'LAKI LAKI') {
            return 'L';
        }
        return null; // tidak terdeteksi -> biarkan kosong (jangan asumsi)
    }
}