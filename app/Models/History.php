<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
#[Fillable(['action', 'file_name', 'user_id','username'])]
class History extends Model
{
    protected $table = 'historials';

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope history entries to the ones a given user is allowed to see,
     * based on their role's area rules.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->role->name) {
            'admin' => $query,
            'gerente' => $query->where(function (Builder $q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('user', fn (Builder $u) => $u->whereIn('area_id', $user->areasGestionadas->pluck('id')));
            }),
            default => abort(403),
        };
    }
}
