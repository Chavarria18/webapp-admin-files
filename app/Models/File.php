<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
#[Fillable(['uuid', 'name', 's3dir', 'size', 'user_id'])]
class File extends Model
{
    use HasFactory;

   use SoftDeletes;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope files to the ones a given user is allowed to see,
     * based on the scope of their role's "files.view" permission.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->permissionScope('files.view')?->constrain($query, $user, 'user_id', 'user')
            ?? $query->whereRaw('1 = 0');
    }
}
