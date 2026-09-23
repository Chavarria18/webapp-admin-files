<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\PermissionScope;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['cognito_sub', 'name', 'email', 'role_id', 'area_id'])]
#[Hidden([ 'remember_token'])]
class User extends Authenticatable
{
     use HasFactory, Notifiable;

    /**
     * The role is needed on almost every request (policies, scopes, navbar).
     */
    protected $with = ['role'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime'];
    }

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Whether the user has any of the given role names.
     */
    public function hasRole(string ...$names): bool
    {
        return in_array($this->role?->name, $names, true);
    }

    /**
     * The scope the user's role grants for an action, or null when it is not granted.
     */
    public function permissionScope(string $action): ?PermissionScope
    {
        return $this->role?->scopeFor($action);
    }

    /**
     * Whether the user's role grants the action at all (any scope).
     */
    public function hasPermission(string $action): bool
    {
        return $this->permissionScope($action) !== null;
    }

    /**
     * Whether the user may perform the action on a record owned by $owner.
     */
    public function canReach(string $action, ?User $owner): bool
    {
        return $this->permissionScope($action)?->allows($this, $owner) ?? false;
    }

    public function area(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function files(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(File::class);
    }

    public function historic(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(History::class);
    }


      public function areasGestionadas(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'gerente_areas', 'gerente_id', 'area_id');
    }

    /**
     * Scope users to the ones a given user is allowed to see,
     * based on the scope of their role's "users.view" permission.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->permissionScope('users.view')?->constrain($query, $user, 'id')
            ?? $query->whereRaw('1 = 0');
    }
}
