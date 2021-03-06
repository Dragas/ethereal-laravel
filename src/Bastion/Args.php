<?php

namespace Ethereal\Bastion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Args
{
    /**
     * Ability name.
     *
     * @var string
     */
    protected $ability;

    /**
     * Model class.
     *
     * @var string
     */
    protected $class;

    /**
     * Model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Model morph class.
     *
     * @var string
     */
    protected $morph;

    /**
     * Payload.
     *
     * @var array
     */
    protected $payload;

    /**
     * Args constructor.
     *
     * @param string $ability
     * @param \Illuminate\Database\Eloquent\Model|string|array|null $model
     * @param array $payload
     */
    public function __construct($ability, $model = null, $payload = [])
    {
        $this->ability = $ability;

        if ($model instanceof Model) {
            $this->class = get_class($model);
            $this->morph = $model->getMorphClass();
            $this->model = $model;
        } elseif (is_string($model)) {
            $this->class = $model;
            $this->morph = Helper::getMorphOfClass($model);
        }

        $this->payload = $payload;
        if (is_array($model)) {
            $this->payload = $model;
        }
    }

    /**
     * Get method name suitable for policy.
     *
     * @return string
     */
    public function method()
    {
        return Str::camel($this->ability);
    }

    /**
     * Get arguments suitable for policy.
     *
     * @return array
     */
    public function arguments()
    {
        $args = [];

        if ($this->model) {
            $args[] = $this->model;
        }

        $args = array_merge($args, $this->payload());

        return $args;
    }

    /**
     * Get ability.
     *
     * @return string
     */
    public function ability()
    {
        return $this->ability;
    }

    /**
     * Get model class.
     *
     * @return string
     */
    public function modelClass()
    {
        return $this->class;
    }

    /**
     * Get model morph.
     *
     * @return string
     */
    public function modelMorph()
    {
        return $this->morph;
    }

    /**
     * Get model.
     *
     * @return array|\Illuminate\Database\Eloquent\Model|null|string
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * Get payload.
     *
     * @return array
     */
    public function payload()
    {
        return $this->payload;
    }
}
