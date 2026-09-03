<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Classes;

echo "--- Mencari Siswa yang Masih Terdaftar di Kelas ---\n";

$classes = Classes::all();

foreach ($classes as $class) {
    $students = User::where('class_id', $class->id)->get();
    
    if ($students->count() > 0) {
        echo "Kelas: {$class->name} (ID: {$class->id})\n";
        echo "Jumlah Siswa: {$students->count()}\n";
        foreach ($students as $student) {
            echo " - {$student->name} (NIS: {$student->nis})\n";
        }
        echo "---------------------------\n";
    }
}
