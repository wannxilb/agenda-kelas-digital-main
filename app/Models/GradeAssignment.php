<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use App\Traits\HasAcademicYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeAssignment extends Model
{
    use HasFactory, HasAcademicYear, BelongsToInstitution;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'title',
        'description',
        'assigned_date',
        'due_date',
        'max_score',
        'academic_year_id',
        'institution_id',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
        'max_score' => 'decimal:2',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function grades()
    {
        return $this->hasMany(StudentGrade::class);
    }
}
