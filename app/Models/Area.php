<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name'])]
class Area extends Model
{
    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function gerentes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'gerente_areas', 'area_id', 'gerente_id');
    }
}
