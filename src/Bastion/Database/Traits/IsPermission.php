<?php

namespace Ethereal\Bastion\Database\Traits;

use Illuminate\Database\Eloquent\Model;

trait IsPermission
{
    /**
     * Create a new permission record.
     *
     * @param string|int $abilityId
     * @param \Illuminate\Database\Eloquent\Model $authority
     * @param string|null $group
     * @param bool $forbids
     * @param \Illuminate\Database\Eloquent\Model|null $parent
     *
     * @return static
     */
    public static function createPermissionRecord($abilityId, Model $authority, $group = null, $forbids = false, Model $parent = null)
    {
        return static::create([
            'ability_id' => $abilityId,
            'target_id' => $authority->getKey(),
            'target_type' => $authority->getMorphClass(),
            'forbidden' => $forbids,
            'group' => $group,
            'parent_id' => $parent ? $parent->getKey() : null,
            'parent_type' => $parent ? $parent->getMorphClass() : null,
        ]);
    }
}
