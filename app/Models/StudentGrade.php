<?php

namespace App\Models;

use App\Observers\StudentGradeObserver;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(StudentGradeObserver::class)]
class StudentGrade extends Model
{
    use HasFactory, BelongsToInstitution;

    protected $fillable = [
        'grade_assignment_id',
        'student_id',
        'score',
        'note',
        'institution_id',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function assignment()
    {
        return $this->belongsTo(GradeAssignment::class, 'grade_assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
