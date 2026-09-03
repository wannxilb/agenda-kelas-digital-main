<?php
use App\Models\User;

$s1 = User::where('name', 'Sekre 1')->first();
if ($s1) $s1->delete();

$s2 = User::where('name', 'Sekre 2')->first();
if ($s2) $s2->delete();

$ahmad = User::where('name', 'Ahmad')->whereHas('class', function($q) {
    $q->where('name', 'XI RPL 1');
})->first();
if ($ahmad) $ahmad->delete();

echo "Deletion processed.";
