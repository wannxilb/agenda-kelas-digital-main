<?php
require __DIR__ . '/vendor/autoload.php';
use App\Models\User;
use App\Models\Classes;
$sek = User::role('sekretaris')->first();
if (!$sek) {
    echo "no sekretaris\n";
    exit;
}
echo "Sekretaris id={$sek->id} class_id={$sek->class_id} inst_id={$sek->institution_id}\n";
if ($sek->class_id) {
    $cls = Classes::find($sek->class_id);
    echo 'class_exists=' . ($cls ? 'true' : 'false');
    if ($cls) {
        echo " cls_inst={$cls->institution_id} cls_name={$cls->name}";
    }
    echo "\n";
}
