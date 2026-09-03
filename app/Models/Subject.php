<?php
// app/Models/Subject.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class Subject extends Model
{
    use HasFactory, BelongsToInstitution;
    
    protected $fillable = [
        'name', 'description', 'credit_hours', 'institution_id'
    ];
    
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'subject_user', 'subject_id', 'user_id');
    }
    
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
    
    public function agendas()
    {
        return $this->hasMany(Agenda::class);
    }
}