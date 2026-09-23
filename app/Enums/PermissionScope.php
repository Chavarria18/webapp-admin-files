<?php

namespace App\Enums;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * How far a role's permission reaches, measured against the user who
 * owns the record (the uploader of a file, the target of a user action...).
 * Every scope includes the actor's own records.
 */
enum PermissionScope: string
{
    case Own = 'own';
    case Area = 'area';
    case Managed = 'managed';
    case All = 'all';

    public function label(): string
    {
        return match ($this) {
            self::Own => 'Propios',
            self::Area => 'Su área',
            self::Managed => 'Áreas gestionadas',
            self::All => 'Todos',
        };
    }

    /**
     * Whether the actor reaches a record owned by the given user.
     */
    public function allows(User $actor, ?User $owner): bool
    {
        if ($this === self::All) {
            return true;
        }

        if (! $owner) {
            return false;
        }

        return $actor->id === $owner->id || match ($this) {
            self::Own => false,
            self::Area => $actor->area_id !== null && $actor->area_id === $owner->area_id,
            self::Managed => $owner->area_id !== null && $actor->areasGestionadas->contains($owner->area_id),
        };
    }

    /**
     * Constrain a query to the records the actor reaches.
     *
     * @param  string  $ownerColumn  column holding the owner's user id ('user_id', or 'id' when querying users)
     * @param  string|null  $ownerRelation  relation to the owner, or null when the query is on users itself
     */
    public function constrain(Builder $query, User $actor, string $ownerColumn, ?string $ownerRelation = null): Builder
    {
        $areaIds = match ($this) {
            self::All, self::Own => null,
            self::Area => array_filter([$actor->area_id]),
            self::Managed => $actor->areasGestionadas->pluck('id')->all(),
        };

        return match ($this) {
            self::All => $query,
            self::Own => $query->where($ownerColumn, $actor->id),
            default => $query->where(function (Builder $q) use ($actor, $ownerColumn, $ownerRelation, $areaIds) {
                $q->where($ownerColumn, $actor->id);

                if ($ownerRelation) {
                    $q->orWhereHas($ownerRelation, fn (Builder $u) => $u->whereIn('area_id', $areaIds));
                } else {
                    $q->orWhereIn('area_id', $areaIds);
                }
            }),
        };
    }
}
