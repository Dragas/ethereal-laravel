<?php

namespace Ethereal\Database;

use Ethereal\Bastion\Bastion;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\Model;

class Ethereal extends Model
{
    use HandlesRelations, Validates;

    const OPTION_SKIP = 1;
    const OPTION_SAVE = 2;
    const OPTION_DELETE = 4;
    const OPTION_ATTACH = 8;
    const OPTION_SYNC = 16;
    const OPTION_DETACH = 32;

    /**
     * Fillable relations.
     *
     * @var string[]
     */
    protected $fillableRelations = [];

    /**
     * Save a new model and return the instance.
     *
     * @param array $attributes
     * @return static
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public static function smartNew(array $attributes = [])
    {
        $model = new static;
        $model->smartFill($attributes);

        return $model;
    }

    /**
     * Fill attributes and relations.
     *
     * @param array $attributes
     * @return $this
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function smartFill(array $attributes)
    {
        $totallyGuarded = $this->totallyGuarded();

        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            $key = $this->removeTableFromKey($key);

            // The developers may choose to place some attributes in the "fillable"
            // array, which means only those attributes may be set through mass
            // assignment to the model, and all others will just be ignored.
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } elseif ($totallyGuarded) {
                throw new MassAssignmentException($key);
            } elseif (in_array($key, $this->fillableRelations, true)) {
                $this->setRelation($key, $value);
            }
        }

        return $this;
    }

    /**
     * Get the fillable attributes of a given array.
     *
     * @param  array $attributes
     * @return array
     */
    protected function fillableFromArray(array $attributes)
    {
        if (count($this->getFillable()) > 0 && ! static::$unguarded) {
            return array_intersect_key($attributes, array_flip(array_merge($this->getFillable(), $this->getFillableRelations())));
        }

        return $attributes;
    }

    /**
     * Get fillable relations array.
     *
     * @return string[]
     */
    public function getFillableRelations()
    {
        return $this->fillableRelations;
    }

    /**
     * Determine if the given attribute may be mass assigned.
     *
     * @param string $key
     * @return bool
     */
    public function isFillable($key)
    {
        if (in_array($key, $this->fillableRelations, true)) {
            return false;
        }

        return parent::isFillable($key);
    }

    /**
     * Save a new model and return the instance.
     *
     * @param array $attributes
     * @return static
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public static function smartCreate(array $attributes = [])
    {
        $model = new static;
        $model->smartFill($attributes);
        $model->smartPush();

        return $model;
    }

    /**
     * Save model and relations. When saving relations, they are linked to this model.
     *
     * @param array $options
     * @return bool|void
     */
    public function smartPush($options = [])
    {
        return $this->saveRelations($options);
    }

    /**
     * Set attribute ignoring setters.
     *
     * @param string $attribute
     * @param mixed $value
     */
    public function setRawAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    /**
     * Sanitize model.
     *
     * @return Ethereal
     */
    public function sanitize()
    {
        app(Bastion::class)->sanitize($this);

        return $this;
    }

    /**
     * Get sanitized model data as array.
     *
     * @return array
     */
    public function sanitizedArray()
    {
        return $this->replicate()->sanitize()->toArray();
    }
}