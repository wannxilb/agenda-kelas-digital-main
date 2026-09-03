<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class Room extends Model
{
    use HasFactory, BelongsToInstitution;

    protected $fillable = [
        'name', 'type', 'capacity', 'is_active', 'institution_id'
    ];
}
