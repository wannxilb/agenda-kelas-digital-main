<?php

namespace App\Policies;

use App\Models\User;

class SekretarisPolicy
{
    public function view(User $user, $model = null): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        $hasRole = $user->hasRole('sekretaris');

        if (is_object($model) && isset($model->institution_id)) {
            return $hasRole && (int) $model->institution_id === (int) $user->institution_id;
        }

        return $hasRole;
    }

    public function manage(User $user): bool
    {
        return $user->hasRole(['super_admin', 'sekretaris']);
    }
}
