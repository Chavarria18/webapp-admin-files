<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view the organigram.
     */
    public function viewOrganigrama(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function viewHistory(User $user,User $target): bool
    {
        
        return match ($user->role->name) {
            'admin' => true,
            'gerente' => $user->id === $target->id
                || $user->areasGestionadas->contains($target->area_id),
            'jefe_area' => false,
            default => false,
        };
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $target): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $target): Response|bool
    {
        if ($user->id === $target->id) {
            return Response::deny('No puedes eliminar tu propio usuario.');
        }

        return $user->hasRole('admin');
    }
}
