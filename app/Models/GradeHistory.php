<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeHistory extends Model
{
    protected $fillable = [
        'student_grade_id',
        'changed_by',
        'old_score',
        'new_score',
        'old_note',
        'new_note',
    ];

    public function studentGrade()
    {
        return $this->belongsTo(StudentGrade::class);
    }

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
