<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory, BelongsToInstitution;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'semester',
        'institution_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function classHistories()
    {
        return $this->hasMany(ClassHistory::class);
    }

    public function agendas()
    {
        return $this->hasMany(Agenda::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
