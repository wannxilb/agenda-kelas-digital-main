<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

// Fetch all names and count
$duplicates = User::select('name', DB::raw('count(*) as count_name'))
    ->groupBy('name')
    ->havingRaw('count(*) > 1')
    ->get();

foreach ($duplicates as $dup) {
    echo "Processing duplicates for: " . $dup->name . PHP_EOL;
    
    // Get all records for this name
    $teachers = User::where('name', $dup->name)->get();
    
    // Check if one has NIP and one does not
    $withNip = $teachers->filter(function($t) { return !empty($t->nip); });
    $withoutNip = $teachers->filter(function($t) { return empty($t->nip); });
    
    if ($withNip->count() > 0 && $withoutNip->count() > 0) {
        foreach ($withoutNip as $teacher) {
            echo "Deleting duplicate without NIP: ID " . $teacher->id . PHP_EOL;
            $teacher->delete();
        }
    } else {
        echo "No clear duplicate without NIP for " . $dup->name . ". Skipping." . PHP_EOL;
    }
}
echo "Finished processing duplicates." . PHP_EOL;
