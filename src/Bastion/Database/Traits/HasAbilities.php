<?php

namespace Ethereal\Bastion\Database\Traits;

use Ethereal\Bastion\Helper;

/**
 * @mixin \Ethereal\Database\Ethereal
 */
trait HasAbilities
{
    /**
     * The abilities relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function abilities()
    {
        return $this->morphToMany(Helper::getAbilityModelClass(), 'entity', Helper::getPermissionTable(), null, 'ability_id');
    }

    /**
     * Give abilities to the model.
     *
     * @param \Illuminate\Database\Eloquent\Model|array|string|int $ability
     * @param \Illuminate\Database\Eloquent\Model|string|null $model
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function allow($ability, $model = null)
    {
        Helper::bastion()->allow($this)->to($ability, $model);

        return $this;
    }

    /**
     * Removes abilities from the model.
     *
     * @param \Illuminate\Database\Eloquent\Model|array|string|int $ability
     * @param \Illuminate\Database\Eloquent\Model|string|null $model
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function disallow($ability, $model = null)
    {
        Helper::bastion()->disallow($this)->to($ability, $model);

        return $this;
    }

    /**
     * Determine if the given ability is granted for the current authority.
     *
     * @param string $ability
     * @param \Illuminate\Database\Eloquent\Model|string|null $model
     * @param array $payload
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function allowed($ability, $model = null, $payload = [])
    {
        return Helper::bastion()->allows($ability, $model, $payload);
    }


    /**
     * Determine if the given ability is denied for the current authority.
     *
     * @param string $ability
     * @param \Illuminate\Database\Eloquent\Model|string|null $model
     * @param array $payload
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function denied($ability, $model = null, $payload = [])
    {
        return Helper::bastion()->denies($ability, $model, $payload);
    }
}
