<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Every check reads the role's permission (and its scope) from the
 * permission_role table, managed by the admin at /permissions.
 */
class UserPolicy
{
    /**
     * Determine whether the user can list users.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.view');
    }

    /**
     * Determine whether the user can view the organigram.
     */
    public function viewOrganigrama(User $user): bool
    {
        return $user->hasPermission('users.organigram');
    }

    public function viewHistory(User $user, User $target): bool
    {
        return $user->canReach('history.view', $target);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('users.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $target): bool
    {
        return $this->mayManage($user, $target) && $user->canReach('users.update', $target);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $target): Response|bool
    {
        if ($user->id === $target->id) {
            return Response::deny('No puedes eliminar tu propio usuario.');
        }

        return $this->mayManage($user, $target) && $user->canReach('users.delete', $target);
    }

    /**
     * Only admins may edit or delete admins, whatever the permission matrix says,
     * so a delegated permission can never be used to take over the admin account.
     */
    private function mayManage(User $user, User $target): bool
    {
        return ! $target->hasRole('admin') || $user->hasRole('admin');
    }
}
