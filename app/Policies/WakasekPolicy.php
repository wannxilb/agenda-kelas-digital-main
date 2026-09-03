<?php

namespace App\Policies;

use App\Models\User;

class WakasekPolicy
{
    public function view(User $user, $model = null): bool
    {
        // Super Admin can view everything
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Check for role
        $hasRole = $user->hasRole('wakasek');

        // If a model instance is provided, check institution match
        if (is_object($model) && isset($model->institution_id)) {
            return $hasRole && (int) $model->institution_id === (int) $user->institution_id;
        }

        return $hasRole;
    }
}
