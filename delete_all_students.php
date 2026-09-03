<?php
// delete_all_students.php
require 'bootstrap/app.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    $students = User::role('siswa')->get();
    echo "Found " . count($students) . " students to delete.\n";
    foreach ($students as $student) {
        // Assuming related records (attendances, class histories) have cascade delete or foreign keys handling it.
        $student->delete();
    }
    echo "All students deleted.\n";
});
