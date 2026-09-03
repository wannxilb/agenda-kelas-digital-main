<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$student = User::where('name', 'Adelio Farrell Julistira')->first();
if ($student) {
    echo 'Student Name: ' . $student->name . PHP_EOL;
    echo 'Student Class ID: ' . ($student->class_id ?? 'NULL') . PHP_EOL;
    echo 'Student Status: ' . ($student->status ?? 'NULL') . PHP_EOL;
} else {
    echo 'Student not found.' . PHP_EOL;
}
