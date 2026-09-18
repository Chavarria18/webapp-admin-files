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
     * based on their role's area/ownership rules.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match ($user->role) {
            'estandar' => $query->where('user_id', $user->id),
            'jefe_area' => $query->whereHas('user', fn (Builder $q) => $q->where('area_id', $user->area_id)),
            'gerente' => $query->whereHas('user', fn (Builder $q) => $q->whereIn('area_id', $user->areasGestionadas->pluck('id'))),
            'admin' => $query,
            default => abort(403),
        };
    }
}
