<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types = 1);

namespace Cawa\Orm;

class CollectionModel extends Collection
{
    /**
     * @var Model[]
     */
    protected $elements;

    /**
     * @var Model[]
     */
    private $added = [];

    /**
     * @var Model[]
     */
    private $removed = [];

    /**
     * @return $this
     */
    public function clearHistory() : self
    {
        $this->removed = [];
        $this->added = [];

        foreach ($this->elements as $element) {
            $element->resetChangedProperties();
        }

        return $this;
    }

    /**
     * @param Model $model
     */
    private function removeFromHistory(Model $model)
    {
        $index = array_search($model, $this->added, true);
        if ($index !== false) {
            unset($this->added[$index]);
        } else {
            $this->removed[] = $model;
        }
    }

    /**
     * @{@inheritdoc}
     */
    public function remove($key)
    {
        $return = parent::remove($key);
        if ($return) {
            $this->removeFromHistory($return);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function removeElement($element) : bool
    {
        $return = parent::removeElement($element);
        if ($return) {
            $this->removeFromHistory($element);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function removeInstance($element) : bool
    {
        $return = parent::removeInstance($element);
        if ($return) {
            $this->removeFromHistory($element);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value) : parent
    {
        if (isset($this->elements[$key])) {
            $this->removeFromHistory($this->elements[$key]);
        }

        $this->added[] = $value;

        return parent::set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function add(...$elements) : parent
    {
        array_push($this->added, ...$elements);

        return parent::add(...$elements);
    }

    /**
     * @param array ...$args
     *
     * @return Collection
     */
    public function save(...$args) : Collection
    {
        $collection = new Collection();

        foreach ($this->added as $current) {
            $return = $current->insert(...$args);
            $collection->add(new ModelSaved($current, $current->getChangedProperties(), $return));
        }

        foreach ($this->removed as $current) {
            $return = $current->delete(...$args);
            $collection->add(new ModelSaved($current, $current->getChangedProperties(), $return));
        }

        foreach ($this->elements as $current) {
            if (in_array($current, $this->added, true)) {
                continue;
            }

            $return = null;
            if (sizeof($current->getChangedProperties()) > 0) {
                $return = $current->update(...$args);
            }
            $collection->add(new ModelSaved($current, $current->getChangedProperties(), $return));
        }

        $this->clearHistory();

        return $collection;
    }
}
