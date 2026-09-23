<?php

namespace App\Models;

use App\Enums\PermissionScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'label'])]
class Role extends Model
{
    /**
     * Roles the given user may hand out. Only admins can assign the admin role,
     * because admins control the permissions screen.
     */
    public static function assignableBy(User $user): Collection
    {
        return static::query()
            ->when(! $user->hasRole('admin'), fn (Builder $q) => $q->where('name', '!=', 'admin'))
            ->orderBy('id')
            ->get();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withPivot('scope')->withTimestamps();
    }

    /**
     * The scope this role has for an action, or null when the role lacks it.
     * Permissions are loaded once per role instance.
     */
    public function scopeFor(string $action): ?PermissionScope
    {
        $permission = $this->loadMissing('permissions')->permissions->firstWhere('action', $action);

        return $permission ? PermissionScope::from($permission->pivot->scope) : null;
    }
}
