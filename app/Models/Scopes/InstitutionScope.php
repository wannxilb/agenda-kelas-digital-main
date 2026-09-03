<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class InstitutionScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Platform Managers/Super Admins see everything.
        // Schools/Institutions see only their own data.
        if (Auth::hasUser() && !Auth::user()->hasRole('super_admin')) {
            $builder->where(function ($query) use ($model) {
                $query->where($model->getTable() . '.institution_id', Auth::user()->institution_id)
                    ->orWhereNull($model->getTable() . '.institution_id');
            });
        }
    }
}
