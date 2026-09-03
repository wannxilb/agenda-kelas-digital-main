<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use App\Traits\HasAcademicYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use BelongsToInstitution, HasAcademicYear, HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'date',
        'status',
        'note',
        'check_in_time',
        'source',
        'academic_year_id',
        'institution_id',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
