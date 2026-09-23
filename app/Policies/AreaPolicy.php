<?php

namespace App\Policies;

use App\Models\User;

class AreaPolicy
{
    /**
     * Determine whether the user can view any areas.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('areas.manage');
    }

    /**
     * Determine whether the user can create areas.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('areas.manage');
    }

    /**
     * Determine whether the user can update the area.
     */
    public function update(User $user): bool
    {
        return $user->hasPermission('areas.manage');
    }

    /**
     * Determine whether the user can delete the area.
     */
    public function delete(User $user): bool
    {
        return $user->hasPermission('areas.manage');
    }
}
