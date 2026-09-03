<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAcademicYear;
use App\Traits\BelongsToInstitution;

class Agenda extends Model
{
    use HasFactory, HasAcademicYear, BelongsToInstitution;

    protected $fillable = [
        'class_id',
        'teacher_id',
        'subject_id',
        'schedule_id',
        'room',
        'date',
        'title',
        'description',
        'attachments',
        'status',
        'academic_year_id',
        'institution_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
