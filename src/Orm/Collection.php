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

class Collection extends Serializable implements CollectionInterface
{
    /**
     * An array containing the entries of this collection.
     *
     * @var array
     */
    protected $elements = [];

    /**
     * Initializes a new ArrayCollection.
     *
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return $this->elements;
    }

    /**
     * {@inheritdoc}
     */
    public function first()
    {
        return reset($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function last()
    {
        return end($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        return next($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        if (!isset($this->elements[$key]) && !array_key_exists($key, $this->elements)) {
            return null;
        }

        $removed = $this->elements[$key];
        unset($this->elements[$key]);

        return $removed;
    }

    /**
     * {@inheritdoc}
     */
    public function removeElement($element) : bool
    {
        $key = array_search($element, $this->elements, true);

        if ($key === false) {
            return false;
        }

        unset($this->elements[$key]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function removeInstance($element) : bool
    {
        $key = array_search($element, $this->elements);

        if ($key === false) {
            return false;
        }

        unset($this->elements[$key]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear() : parent
    {
        $this->elements = [];

        return $this;
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->containsKey($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (!isset($offset)) {
            return $this->add($value);
        }

        return $this->set($offset, $value);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        return $this->remove($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function containsKey($key) : bool
    {
        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($element) : bool
    {
        return in_array($element, $this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function containsInstance($element) : bool
    {
        return in_array($element, $this->elements, true);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(callable $callable) : bool
    {
        foreach ($this->elements as $key => $element) {
            if ($callable($key, $element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compare with current collection, add missing, remove unnecessary on current
     *
     * @param Collection $collection
     *
     * @return $this
     */
    public function diff(Collection $collection) : self
    {
        foreach ($this->elements as $element) {
            if (!$collection->contains($element)) {
                $this->removeElement($element);
            }
        }

        foreach ($collection as $element) {
            if (!$this->contains($element)) {
                $this->add($element);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function indexOf($element)
    {
        return array_search($element, $this->elements, true);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return isset($this->elements[$key]) ? $this->elements[$key] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys() : array
    {
        return array_keys($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function getValues() : array
    {
        return array_values($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function count() : int
    {
        return count($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value) : parent
    {
        $this->elements[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function add(...$elements) : parent
    {
        foreach ($elements as $element) {
            $this->elements[] = $element;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty() : bool
    {
        return empty($this->elements);
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * {@inheritdoc}
     */
    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(callable $callable) : parent
    {
        return new static(array_map($callable, $this->elements));
    }

    /**
     * {@inheritdoc}
     */
    public function call(string $method, ... $vars) : parent
    {
        return new static(array_map(function ($element) use ($method, $vars) {
            return call_user_func_array([$element, $method], $vars);
        }, $this->elements));
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $callable) : parent
    {
        return new static(array_filter($this->elements, $callable, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * @param string $method property or method
     * @param mixed $value the comparison value
     *
     * @return $this
     */
    public function find(string $method, $value)
    {
        $isMethod = null;

        return new static(array_filter($this->elements, function ($item) use ($method, $value, &$isMethod) {
            if (is_null($isMethod)) {
                $isMethod = method_exists($item, $method);
            }

            if ($isMethod) {
                return call_user_func([$item, $method]) === $value;
            } else {
                return $item->$method === $value;
            }
        }));
    }

    /**
     * @param string $method property or method
     * @param mixed $value the comparison value
     *
     * @return mixed|null
     */
    public function findOne(string $method, $value)
    {
        $return = $this->find($method, $value);

        if ($return->count() > 1) {
            throw new \OverflowException(sprintf(
                "Too many element return (%s), needed only one for '%s' = '%s'",
                $return->count(),
                $method,
                is_object($value) ? get_class($value) : $value
            ));
        }

        return $return->count() ? $return->first() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function forAll(callable $callable) : bool
    {
        foreach ($this->elements as $key => $element) {
            if (!$callable($key, $element)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function partition(callable $callable) : array
    {
        $matches = $noMatches = [];

        foreach ($this->elements as $key => $element) {
            if ($callable($key, $element)) {
                $matches[$key] = $element;
            } else {
                $noMatches[$key] = $element;
            }
        }

        return [new static($matches), new static($noMatches)];
    }

    /**
     * {@inheritdoc}
     */
    public function slice($offset, $length = null) : parent
    {
        return new static(array_slice($this->elements, $offset, $length, true));
    }

    /**
     * Merge current collection and passed collection.
     * Return a new collection instance and don't alter current one.
     *
     * @param Collection $collection
     *
     * @return $this
     */
    public function merge(Collection $collection) : self
    {
        $return = clone $this;
        $return->elements = array_merge($return->elements, $collection->elements);

        return $return;
    }

    /**
     * Sort current collection with a callable base on values
     * Key association is not maintains
     *
     * @param callable $sortFunction
     *
     * @return $this|Collection
     */
    public function sort(callable $sortFunction) : self
    {
        usort($this->elements, $sortFunction);

        return $this;
    }

    /**
     * Sort current collection with a callable base on values
     * Key association is maintains
     *
     * @param callable $sortFunction
     *
     * @return $this|Collection
     */
    public function sortByKey(callable $sortFunction) : self
    {
        uksort($this->elements, $sortFunction);

        return $this;
    }

    /**
     * Sort current collection with a callable base on values
     * Key association is maintains
     *
     * @param callable $sortFunction
     *
     * @return $this|Collection
     */
    public function sortAssociative(callable $sortFunction) : self
    {
        uasort($this->elements, $sortFunction);

        return $this;
    }

    /**
     * @param string $method property or method
     *
     * @return $this
     */
    public function getDistinct(string $method)
    {
        $isMethod = null;

        $array = [];

        foreach ($this->elements as $item) {
            if (is_null($isMethod)) {
                $isMethod = method_exists($item, $method);
            }

            if ($isMethod) {
                $value = call_user_func([$item, $method]);
            } else {
                $value = $item->$method;
            }

            if (!in_array($value, $array, true)) {
                $array[] = $value;
            }
        }

        return new static(array_unique($array, SORT_REGULAR));
    }
}
