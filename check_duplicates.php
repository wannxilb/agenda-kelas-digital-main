<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "--- Mencari Nama Guru Ganda ---\n";

$teachers = User::role('teacher')->get();
$duplicates = $teachers->groupBy('name')->filter(function($group) {
    return $group->count() > 1;
});

if ($duplicates->isEmpty()) {
    echo "Tidak ditemukan nama guru yang ganda.\n";
} else {
    foreach ($duplicates as $name => $items) {
        echo "\nNama: $name (" . $items->count() . " kali ditemukan)\n";
        foreach ($items as $item) {
            echo "  - ID: {$item->id}\n";
            echo "    Email: {$item->email}\n";
            echo "    Created: {$item->created_at}\n";
        }
    }
}

echo "\n--- Mencari Email Guru Ganda ---\n";
$emailDuplicates = $teachers->groupBy('email')->filter(function($group) {
    return $group->count() > 1;
});

if ($emailDuplicates->isEmpty()) {
    echo "Tidak ditemukan email guru yang ganda.\n";
} else {
    foreach ($emailDuplicates as $email => $items) {
        echo "\nEmail: $email (" . $items->count() . " kali ditemukan)\n";
        foreach ($items as $item) {
            echo "  - ID: {$item->id}\n";
            echo "    Nama: {$item->name}\n";
        }
    }
}
