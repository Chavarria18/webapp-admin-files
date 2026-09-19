<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, File $file): bool
    {
        return $this->hasAccessTo($user, $file);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, File $file): bool
    {
        return $this->hasAccessTo($user, $file);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, File $file): bool
    {
        return match ($user->role) {
            'admin' => true,
            'gerente' => $user->id === $file->user_id
                || $user->areasGestionadas->contains($file->user->area_id),
            'jefe_area' => $user->id === $file->user_id,
            default => $user->id === $file->user_id,
        };
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, File $file): bool
    {
        return $this->delete($user, $file);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, File $file): bool
    {
        return $this->delete($user, $file);
    }

    /**
     * Shared area/ownership scoping used by every action on a file.
     */
    private function hasAccessTo(User $user, File $file): bool
    {
        return match ($user->role) {
            'admin' => true,
            'gerente' => $user->id === $file->user_id
                || $user->areasGestionadas->contains($file->user->area_id),
            'jefe_area' => $user->area_id === $file->user->area_id,
            default => $user->id === $file->user_id,
        };
    }
}
