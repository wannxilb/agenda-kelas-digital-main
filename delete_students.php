<?php

use App\Models\User;

\$studentsToDelete = User::whereHas('roles', function(\$query) {
    \$query->where('name', 'siswa');
})->whereHas('class', function(\$query) {
    \$query->where('name', 'NOT LIKE', '%PPLG%')
          ->where('name', 'NOT LIKE', '%RPL%');
})->get();

foreach (\$studentsToDelete as \$student) {
    \$student->delete();
}

echo 'Deleted ' . \$studentsToDelete->count() . ' students.';
