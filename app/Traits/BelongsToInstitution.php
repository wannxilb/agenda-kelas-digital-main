<?php

namespace App\Traits;

use App\Models\Institution;
use App\Models\Scopes\InstitutionScope;

trait BelongsToInstitution
{
    protected static function bootBelongsToInstitution()
    {
        static::addGlobalScope(new InstitutionScope);

        static::creating(function ($model) {
            if (auth()->hasUser() && !$model->institution_id) {
                $model->institution_id = auth()->user()->institution_id;
            }
        });
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
