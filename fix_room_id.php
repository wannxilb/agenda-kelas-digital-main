<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = \App\Models\Schedule::where('day', 'Thursday')->first();
$r = \App\Models\Room::where('name', 'Laboratorium Komputer 6')->first();
if($s && $r) {
    $s->room_id = $r->id;
    $s->save();
    echo 'Schedule ' . $s->id . ' updated to Room ID ' . $r->id . PHP_EOL;
} else {
    echo 'Data tidak ditemukan' . PHP_EOL;
}
