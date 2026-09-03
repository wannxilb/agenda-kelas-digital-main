<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Classes;

$students = User::role('siswa')->get();
$firstStudent = $students->first();

if ($firstStudent) {
    echo 'First Student: ' . $firstStudent->name . PHP_EOL;
    echo '  User class_id: ' . ($firstStudent->class_id ?? 'NULL') . PHP_EOL;
    echo '  User institution_id: ' . ($firstStudent->institution_id ?? 'NULL') . PHP_EOL;
    
    // Query class using find without scope (using raw DB) to see if it exists
    $classRaw = \DB::table('classes')->where('id', $firstStudent->class_id)->first();
    if ($classRaw) {
        echo '  Class Name: ' . $classRaw->name . PHP_EOL;
        echo '  Class institution_id: ' . ($classRaw->institution_id ?? 'NULL') . PHP_EOL;
    } else {
        echo '  Class not found in raw DB.' . PHP_EOL;
    }
    
    // Query class using model (with scope)
    $classModel = Classes::find($firstStudent->class_id);
    if ($classModel) {
        echo '  Class Model retrieved successfully.' . PHP_EOL;
    } else {
        echo '  Class Model NOT retrieved (likely due to global scope).' . PHP_EOL;
    }
} else {
    echo 'No student found.' . PHP_EOL;
}
