<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$idsToDelete = [464, 466, 467];

echo "--- Proses Penghapusan Guru Duplikat (Gmail) ---\n";

foreach ($idsToDelete as $id) {
    $user = User::find($id);
    if ($user) {
        echo "Menghapus ID: {$id} | Nama: {$user->name} | Email: {$user->email}\n";
        
        try {
            DB::transaction(function() use ($user) {
                // Hapus roles/permissions dulu (Spatie)
                $user->roles()->detach();
                $user->permissions()->detach();
                
                // Hapus user
                $user->delete();
            });
            echo "✓ Berhasil dihapus.\n";
        } catch (\Exception $e) {
            echo "✗ Gagal menghapus: " . $e->getMessage() . "\n";
        }
    } else {
        echo "ID: {$id} tidak ditemukan.\n";
    }
}

echo "\nSelesai.\n";
