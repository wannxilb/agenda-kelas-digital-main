<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    use HasFactory, BelongsToInstitution;

    protected $fillable = [
        'name', 'code', 'is_active', 'institution_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function gradeTemplates()
    {
        return $this->hasMany(MajorGradeTemplate::class, 'major_id');
    }

    public function classes()
    {
        return $this->hasMany(Classes::class, 'major', 'name');
    }
}
