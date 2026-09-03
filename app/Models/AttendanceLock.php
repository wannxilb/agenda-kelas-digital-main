<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class AttendanceLock extends Model
{
    use HasFactory, BelongsToInstitution;

    protected $fillable = [
        'class_id',
        'date',
        'is_locked',
        'institution_id',
    ];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
