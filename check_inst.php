<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Institution;
use App\Models\User;

$inst = Institution::first();
echo "Institution: " . ($inst ? $inst->name : 'None') . "\n";
echo "Admins: " . User::role('admin')->where('institution_id', $inst->id ?? 0)->count() . "\n";
