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
    private $elements;

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
    public function toArray()
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
    public function removeElement($element)
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
    public function clear()
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
    public function containsKey($key)
    {
        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($element)
    {
        return in_array($element, $this->elements, true);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(callable $callable)
    {
        foreach ($this->elements as $key => $element) {
            if ($callable($key, $element)) {
                return true;
            }
        }

        return false;
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
    public function getKeys()
    {
        return array_keys($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function getValues()
    {
        return array_values($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->elements[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function add($element)
    {
        $this->elements[] = $element;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->elements);
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(callable $callable)
    {
        return new static(array_map($callable, $this->elements));
    }

    /**
     * {@inheritdoc}
     */
    public function call(string $method, ... $vars)
    {
        return new static(array_map(function ($element) use ($method, $vars) {

            return call_user_func_array([$element, $method], $vars);
        }, $this->elements));
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $callable)
    {
        return new static(array_filter($this->elements, $callable, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * @param string $method property or method
     * @param mixed $value the comparison value
     *
     * @return static
     */
    public function find(string $method, $value)
    {
        return new static(array_filter($this->elements, function ($item) use ($method, $value) {
            if (method_exists($item, $method)) {
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
    public function forAll(callable $callable)
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
    public function partition(callable $callable)
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
    public function slice($offset, $length = null)
    {
        return array_slice($this->elements, $offset, $length, true);
    }
}
