<?php

namespace Ethereal\Database\Relations\Handlers;

use Ethereal\Database\Relations\Manager;

class BelongsToHandler extends Handler
{
    /**
     * Wrap data into model or collection of models based on relation type.
     *
     * @return \Ethereal\Database\Ethereal|\Illuminate\Database\Eloquent\Collection
     * @throws \Ethereal\Database\Relations\Exceptions\InvalidTypeException
     */
    public function build()
    {
        $model = $this->hydrateModel($this->data);
        $this->validate();

        return $model;
    }

    /**
     * Save relation data.
     *
     * @return bool
     * @throws \Ethereal\Database\Relations\Exceptions\InvalidTypeException
     * @throws \Exception
     */
    public function save()
    {
        $model = $this->build();

        if ($this->options & Manager::SAVE) {
            $model->save();
            $this->relation->associate($model);
        }

        if ($this->options & Manager::DELETE && $model->exists) {
            $model->delete();
            $this->relation->dissociate($model);
        }

        return true;
    }

    /**
     * Check if the relation is waiting for parent model to be saved.
     *
     * @return bool
     */
    public function isWaitingForParent()
    {
        return false;
    }
}
