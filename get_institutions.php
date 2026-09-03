<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;

$institutions = Institution::all();
echo 'Total Institutions: ' . $institutions->count() . PHP_EOL;
foreach ($institutions as $inst) {
    echo 'ID: ' . $inst->id . ' | Name: ' . $inst->name . PHP_EOL;
}
