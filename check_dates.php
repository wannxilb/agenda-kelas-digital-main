<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Attendance;

$results = Attendance::where('date', 'like', '% %')->get(['id', 'date']);
echo "Found " . count($results) . " records with long date format.\n";
foreach ($results as $r) {
    echo "ID: {$r->id}, Date: {$r->date}\n";
}
