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
     * based on the scope of their role's "history.view" permission.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->permissionScope('history.view')?->constrain($query, $user, 'user_id', 'user')
            ?? $query->whereRaw('1 = 0');
    }
}
