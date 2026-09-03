<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Classes;

$sek = User::role('sekretaris')->first();
if (!$sek) {
    echo "no sekretaris\n";
    exit(1);
}
echo "sek id={$sek->id} class_id={$sek->class_id} inst_id=" . ($sek->institution_id ?? 'NULL') . "\n";
if ($sek->class_id) {
    $cls = Classes::withoutGlobalScopes()->find($sek->class_id);
    echo "cls_without_scope=" . ($cls ? 'true' : 'false') . "\n";
    if ($cls) {
        echo "cls id={$cls->id} inst_id=" . ($cls->institution_id ?? 'NULL') . " name={$cls->name}\n";
    }
    $cls2 = Classes::find($sek->class_id);
    echo "cls_with_scope=" . ($cls2 ? 'true' : 'false') . "\n";
}
