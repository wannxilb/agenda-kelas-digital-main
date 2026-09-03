<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$sekretaris = User::role('sekretaris')->get();
echo 'Total Secretaries: ' . $sekretaris->count() . PHP_EOL;

$nullCount = 0;
foreach($sekretaris as $s) {
    if (is_null($s->institution_id)) {
        $nullCount++;
    }
}
echo 'Secretaries with NULL institution_id: ' . $nullCount . PHP_EOL;

if ($sekretaris->isNotEmpty()) {
    $firstSek = $sekretaris->first();
    echo 'First Secretary: ' . $firstSek->name . PHP_EOL;
    echo '  User class_id: ' . ($firstSek->class_id ?? 'NULL') . PHP_EOL;
    echo '  User institution_id: ' . ($firstSek->institution_id ?? 'NULL') . PHP_EOL;
}
