<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * An action the application checks (e.g. "files.delete").
 * Scoped actions apply to records owned by someone; unscoped ones are plain yes/no.
 */
#[Fillable(['action', 'label', 'group', 'scoped'])]
class Permission extends Model
{
    protected function casts(): array
    {
        return ['scoped' => 'boolean'];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withPivot('scope')->withTimestamps();
    }
}
