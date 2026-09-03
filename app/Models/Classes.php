<?php
// app/Models/Classes.php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory, BelongsToInstitution;
    
    protected $table = 'classes';
    
    protected $fillable = [
        'name', 'major', 'grade_level', 'academic_year',
        'homeroom_teacher_id', 'capacity', 'description', 'is_active', 'institution_id'
    ];
    
    public function homeroomTeacher()
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }
    
    public function students()
    {
        return $this->hasMany(User::class, 'class_id')->where('status', 'active')->orderByRaw('LOWER(name) ASC');
    }
    
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }
    
    public function agendas()
    {
        return $this->hasMany(Agenda::class, 'class_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    public function classHistories()
    {
        return $this->hasMany(ClassHistory::class, 'class_id');
    }
}
