<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$roles = ['super_admin', 'adm


in', 'teacher', 'siswa', 'sekretaris', 'walikelas', 'wakasek'];
foreach ($roles as $role) {
    try {
        $users = User::role($role)->get();
        $total = $users->count();
        $nullCount = $users->whereNull('institution_id')->count();
        echo "Role: $role | Total: $total | NULL institution_id: $nullCount" . PHP_EOL;
    } catch (\Exception $e) {
        // Some roles might not exist or have different names
        echo "Role: $role | Error: " . $e->getMessage() . PHP_EOL;
    }
}


