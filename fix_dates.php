<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

$results = DB::table('attendances')->where('date', 'like', '% %')->get(['id', 'date']);
echo "Found " . count($results) . " records to fix.\n";
foreach ($results as $r) {
    $newDate = substr($r->date, 0, 10);
    DB::table('attendances')->where('id', $r->id)->update(['date' => $newDate]);
    echo "Updated ID: {$r->id} from {$r->date} to {$newDate}\n";
}
