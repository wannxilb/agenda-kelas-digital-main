<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

// List all teachers to help identify the extra ones
$teachers = User::role('teacher')->orderBy('name')->get();

foreach ($teachers as $teacher) {
    echo $teacher->id . " | " . $teacher->name . " | " . ($teacher->nip ?? 'NO NIP') . PHP_EOL;
}
