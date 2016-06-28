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

class Collection implements \Countable, \IteratorAggregate, \ArrayAccess
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
     * Reset ArrayCollection with this array.
     *
     * @param array $elements
     *
     * @return $this
     */
    public function fromArray(array $elements = []) : self
    {
        $this->elements = $elements;
    }

    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return array
     */
    public function toArray() : array
    {
        return $this->elements;
    }

    /**
     * Sets the internal iterator to the first element in the collection and returns this element.
     *
     * @return mixed
     */
    public function first()
    {
        return reset($this->elements);
    }

    /**
     * Sets the internal iterator to the last element in the collection and returns this element.
     *
     * @return mixed
     */
    public function last()
    {
        return end($this->elements);
    }

    /**
     * Gets the key/index of the element at the current iterator position.
     *
     * @return int|string
     */
    public function key()
    {
        return key($this->elements);
    }

    /**
     * Moves the internal iterator position to the next element and returns this element.
     *
     * @return mixed
     */
    public function next()
    {
        return next($this->elements);
    }

    /**
     * Gets the element of the collection at the current iterator position.
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->elements);
    }

    /**
     * Removes the element at the specified index from the collection.
     *
     * @param string|int $key The kex/index of the element to remove.
     *
     * @return mixed The removed element or NULL, if the collection did not contain the element.
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
     * Removes the specified element from the collection, if it is found.
     * The comparison is not strict (==), they have the same attributes and values,
     * and are instances of the same class.
     *
     * @param mixed $element The element to remove.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
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
     * Removes the specified element from the collection, if it is found.
     * The comparison is strict (===), they refer to the same instance of the same class.
     *
     * @param mixed $element The element to remove.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
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
     * Remove from current all element find by property or method value
     *
     * @param string $method property or method
     * @param mixed $value the comparison value
     *
     * @return $this a new collection with remove element
     */
    public function removeFind(string $method, $value) : self
    {
        $list = $this->find($method, $value);
        foreach ($list as $element) {
            $this->removeInstance($element);
        }

        return $list;
    }


    /**
     * Clears the collection, removing all elements.
     *
     * @return $this
     */
    public function clear() : self
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
     * Checks whether the collection contains an element with the specified key/index.
     *
     * @param string|int $key The key/index to check for.
     *
     * @return bool TRUE if the collection contains an element with the specified key/index,
     *              FALSE otherwise.
     */
    public function containsKey($key) : bool
    {
        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }

    /**
     * Checks whether an element is contained in the collection.
     * The comparison is not strict (==), they have the same attributes and values,
     * and are instances of the same class.
     * This is an O(n) operation, where n is the size of the collection.
     *
     * @param mixed $element The element to search for.
     *
     * @return bool TRUE if the collection contains the element, FALSE otherwise.
     */
    public function contains($element) : bool
    {
        return in_array($element, $this->elements);
    }

    /**
     * Checks whether an reference is contained in the collection.
     * The comparison is strict (===), they refer to the same instance of the same class.
     * This is an O(n) operation, where n is the size of the collection.
     *
     * @param mixed $element The element to search for.
     *
     * @return bool TRUE if the collection contains the element, FALSE otherwise.
     */
    public function containsInstance($element) : bool
    {
        return in_array($element, $this->elements, true);
    }

    /**
     * Tests for the existence of an element that satisfies the given predicate.
     *
     * @param callable $callable The predicate.
     *
     * @return bool TRUE if the predicate is TRUE for at least one element, FALSE otherwise.
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
     * Gets the index/key of a given element. The comparison of two elements is strict,
     * that means not only the value but also the type must match.
     * For objects this means reference equality.
     *
     * @param mixed $element The element to search for.
     *
     * @return int|string|bool The key/index of the element or FALSE if the element was not found.
     */

    public function indexOf($element)
    {
        return array_search($element, $this->elements, true);
    }

    /**
     * Gets the element at the specified key/index.
     *
     * @param string|int $key The key/index of the element to retrieve.
     *
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->elements[$key]) ? $this->elements[$key] : null;
    }

    /**
     * Gets all keys/indices of the collection.
     *
     * @return array The keys/indices of the collection, in the order of the corresponding
     *               elements in the collection.
     */
    public function getKeys() : array
    {
        return array_keys($this->elements);
    }

    /**
     * Reset all keys/indices of the collection.
     * To used only on numeric index !
     *
     * @return $this
     */
    public function resetIndex() : self
    {
        $this->elements = array_values($this->elements);

        return $this;
    }

    /**
     * Gets all values of the collection.
     *
     * @return array The values of all elements in the collection, in the order they
     *               appear in the collection.
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
     * Sets an element in the collection at the specified key/index.
     *
     * @param string|int $key   The key/index of the element to set.
     * @param mixed      $value The element to set.
     *
     * @return $this
     */
    public function set($key, $value) : self
    {
        $this->elements[$key] = $value;

        return $this;
    }

    /**
     * Adds an element at the end of the collection.

     * @param array $elements The elements to add.
     *
     * @return $this
     */
    public function add(...$elements) : self
    {
        foreach ($elements as $element) {
            $this->elements[] = $element;
        }

        return $this;
    }


    /**
     * Checks whether the collection is empty (contains no elements).
     *
     * @return bool TRUE if the collection is empty, FALSE otherwise.
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
     * Applies the given function to each element in the collection and returns
     * a new collection with the elements returned by the function.
     *
     * @param callable $callable
     *
     * @return static
     */
    public function apply(callable $callable) : self
    {
        return new static(array_map($callable, $this->elements));
    }

    /**
     * Call the given method to each element in the collection and returns
     * a new collection with return values for each call
     *
     * @param string $method
     * @param mixed ...$vars
     *
     * @return static
     */
    public function call(string $method, ... $vars) : self
    {
        return new static(array_map(function ($element) use ($method, $vars) {
            return call_user_func_array([$element, $method], $vars);
        }, $this->elements));
    }

    /**
     * Returns all the elements of this collection that satisfy the callable $callable.
     * The order of the elements is preserved.
     *
     * @param callable $callable The predicate used for filtering.
     *
     * @return static A collection with the results of the filter operation.
     */
    public function filter(callable $callable) : self
    {
        return new static(array_filter($this->elements, $callable, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Return a new collection find by property or method value
     *
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
     * Return one element find by property or method value
     *
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
     * Tests whether the given callable $callable holds for all elements of this collection.
     *
     * @param callable $callable The predicate.
     *
     * @return bool TRUE, if the predicate yields TRUE for all elements, FALSE otherwise.
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
     * Partitions this collection in two collections according to a predicate.
     * Keys are preserved in the resulting collections.
     *
     * @param callable $callable $p The predicate on which to partition.
     *
     * @return static[] An array with two elements. The first element contains the collection
     *     of elements where the predicate returned TRUE, the second element
     *     contains the collection of elements where the predicate returned FALSE.
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
     * Extracts a slice of $length elements starting at position $offset from the Collection.
     *
     * If $length is null it returns all elements from $offset to the end of the Collection.
     * Keys have to be preserved by this method. Calling this method will only return the
     * selected slice and NOT change the elements contained in the collection slice is called on.
     *
     * @param int      $offset The offset to start from.
     * @param int|null $length The maximum number of elements to return, or null for no limit.
     *
     * @return static
     */
    public function slice($offset, $length = null) : self
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
     * @return $this
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
     * @return $this
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
     * @return $this
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

    /**
     * @return $this
     */
    public function shuffle() : self
    {
        $return = new static($this->elements);
        shuffle($return->elements);

        return $return;
    }
}
