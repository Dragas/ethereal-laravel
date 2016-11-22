<?php

namespace Ethereal\Bastion\Conductors;

use Ethereal\Bastion\Database\Contracts\RoleContract;
use Ethereal\Bastion\Helper;

class RemovesAbilities
{
    use Traits\ClearsCache;

    /**
     * List of authorities to remove abilities from.
     *
     * @var array|string
     */
    protected $authorities;

    /**
     * Permission store.
     *
     * @var \Ethereal\Bastion\Store\Store
     */
    protected $store;

    /**
     * AssignsRole constructor.
     *
     * @param \Ethereal\Bastion\Store\Store $store
     * @param string|int|array $authorities
     */
    public function __construct($store, $authorities)
    {
        $this->authorities = $authorities;
        $this->store = $store;
    }

    /**
     * Give abilities to authorities.
     *
     * @param \Illuminate\Database\Eloquent\Model|array|string|int $abilities
     * @param \Illuminate\Database\Eloquent\Model|string|null $model
     *
     * @throws \InvalidArgumentException
     */
    public function to($abilities, $model = null)
    {
        /** @var \Ethereal\Bastion\Database\Ability $abilityClass */
        $abilityClass = Helper::getAbilityModelClass();
        /** @var \Ethereal\Bastion\Database\Role $roleModelClass */
        $roleModelClass = Helper::getRoleModelClass();

        $clearAll = false;
        $abilityIds = $abilityClass::collectAbilities((array)$abilities, $model)->pluck('id');

        if ($abilityIds->isEmpty()) {
            return;
        }

        foreach ($this->authorities as $authority) {
            if (is_string($authority)) {
                $authority = $roleModelClass::where('name', $authority)->first();

                if (!$authority) {
                    continue;
                }
            }

            if ($authority instanceof RoleContract) {
                $clearAll = true;
            }

            $authority->abilities()->detach($abilityIds->all());
        }

        $this->clearCache($this->store, $clearAll, $this->authorities);
    }
}
