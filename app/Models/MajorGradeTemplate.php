<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MajorGradeTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'major_id', 'grade_level', 'code', 'rombel_count'
    ];

    public function major()
    {
        return $this->belongsTo(Major::class);
    }
}
