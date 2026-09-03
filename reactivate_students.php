<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Classes;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

$class = Classes::where('name', 'XII AK 1')->first();

if (!$class) {
    echo "Class 'XII AK 1' not found.\n";
    exit;
}

echo "Found class 'XII AK 1' (ID: {$class->id}). Searching for students...\n";

// Assuming we want to reactivate students who belong to this class_id
// Note: Since I deleted all users with role 'siswa' in the previous turn,
// this might not return anything unless those users were not actually deleted 
// or if I need to check something else.
$students = User::where('class_id', $class->id)->get();

if ($students->isEmpty()) {
    echo "No students found for class 'XII AK 1'.\n";
    exit;
}

$count = 0;
foreach ($students as $student) {
    $student->update(['status' => 'active']);
    $count++;
}

Cache::forget('stats_male_students_active_all');
Cache::forget('stats_female_students_active_all');

echo "Successfully reactivated {$count} students from class 'XII AK 1'.\n";
