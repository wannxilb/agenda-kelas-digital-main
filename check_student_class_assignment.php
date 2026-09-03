<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$students = User::role('siswa')->get();
$nullCount = 0;
foreach($students as $s) {
    if (is_null($s->class_id)) {
        $nullCount++;
    }
}

echo 'Total Students: ' . $students->count() . PHP_EOL;
echo 'Students with NULL class_id: ' . $nullCount . PHP_EOL;

$firstStudent = $students->first();
if ($firstStudent) {
    echo 'First student: ' . $firstStudent->name . PHP_EOL;
    echo 'Class Histories count: ' . $firstStudent->classHistories()->count() . PHP_EOL;
}
