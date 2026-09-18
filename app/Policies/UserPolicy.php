<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view the organigram.
     */
    public function viewOrganigrama(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function viewHistory(User $user,User $target): bool
    {
        
        return match ($user->role) {
            'admin' => true,
            'gerente' => $user->areasGestionadas->contains($target->area_id),
            'jefe_area' => false,
            default => false,
        };
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $target): bool
    {
        return $this->hasAccessTo($user, $target);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $target): bool
    {
        return $this->hasAccessTo($user, $target);
    }

    /**
     * Shared area scoping used by every action on a target user.
     */
    private function hasAccessTo(User $user, User $target): bool
    {
        return match ($user->role) {
            'admin' => true,
            'gerente' => $user->areasGestionadas->contains($target->area_id),
            'jefe_area' => $user->area_id === $target->area_id,
            default => false,
        };
    }
}
