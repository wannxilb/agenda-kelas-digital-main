<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Classes;

Classes::all()->each(function($class) {
    $parts = explode(' ', $class->name);
    // Assuming name format is "Level Major Number" like "XI RPL 2"
    $class->major = isset($parts[1]) ? $parts[1] : 'Umum';
    $class->save();
    echo "Updated {$class->name} to major {$class->major}\n";
});
