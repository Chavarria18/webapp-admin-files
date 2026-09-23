<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['cognito_sub', 'name', 'email', 'role', 'area_id'])]
#[Hidden([ 'remember_token'])]
class User extends Authenticatable
{
     use HasFactory, Notifiable;

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime'];
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
     * based on their role's area rules.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->role) {
            'admin' => $query,
             'gerente' => $query->where(function (Builder $q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('user', fn (Builder $u) => $u->whereIn('area_id', $user->areasGestionadas->pluck('id')));
            }),
            'jefe_area' => $query->where('area_id', $user->area_id),
            default => abort(403),
        };
    }
}
