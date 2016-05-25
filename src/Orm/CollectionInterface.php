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

interface CollectionInterface extends \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * Adds an element at the end of the collection.

     * @param array $elements The elements to add.
     *
     * @return $this|CollectionInterface
     */
    public function add(...$elements) : self;

    /**
     * Clears the collection, removing all elements.
     *
     * @return $this
     */
    public function clear() : self;

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
    public function contains($element) : bool ;

    /**
     * Checks whether an reference is contained in the collection.
     * The comparison is strict (===), they refer to the same instance of the same class.
     * This is an O(n) operation, where n is the size of the collection.
     *
     * @param mixed $element The element to search for.
     *
     * @return bool TRUE if the collection contains the element, FALSE otherwise.
     */
    public function containsInstance($element) : bool ;

    /**
     * Checks whether the collection is empty (contains no elements).
     *
     * @return bool TRUE if the collection is empty, FALSE otherwise.
     */
    public function isEmpty() : bool;

    /**
     * Removes the element at the specified index from the collection.
     *
     * @param string|int $key The kex/index of the element to remove.
     *
     * @return mixed The removed element or NULL, if the collection did not contain the element.
     */
    public function remove($key);

    /**
     * Removes the specified element from the collection, if it is found.
     * The comparison is not strict (==), they have the same attributes and values,
     * and are instances of the same class.
     *
     * @param mixed $element The element to remove.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeElement($element) : bool;

    /**
     * Removes the specified element from the collection, if it is found.
     * The comparison is strict (===), they refer to the same instance of the same class.
     *
     * @param mixed $element The element to remove.
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeInstance($element) : bool;

    /**
     * Checks whether the collection contains an element with the specified key/index.
     *
     * @param string|int $key The key/index to check for.
     *
     * @return bool TRUE if the collection contains an element with the specified key/index,
     *              FALSE otherwise.
     */
    public function containsKey($key) : bool ;

    /**
     * Gets the element at the specified key/index.
     *
     * @param string|int $key The key/index of the element to retrieve.
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Gets all keys/indices of the collection.
     *
     * @return array The keys/indices of the collection, in the order of the corresponding
     *               elements in the collection.
     */
    public function getKeys() : array;

    /**
     * Gets all values of the collection.
     *
     * @return array The values of all elements in the collection, in the order they
     *               appear in the collection.
     */
    public function getValues() : array ;

    /**
     * Sets an element in the collection at the specified key/index.
     *
     * @param string|int $key   The key/index of the element to set.
     * @param mixed      $value The element to set.
     *
     * @return $this
     */
    public function set($key, $value) : self;

    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return array
     */
    public function toArray() : array ;

    /**
     * Sets the internal iterator to the first element in the collection and returns this element.
     *
     * @return mixed
     */
    public function first();

    /**
     * Sets the internal iterator to the last element in the collection and returns this element.
     *
     * @return mixed
     */
    public function last();

    /**
     * Gets the key/index of the element at the current iterator position.
     *
     * @return int|string
     */
    public function key();

    /**
     * Gets the element of the collection at the current iterator position.
     *
     * @return mixed
     */
    public function current();

    /**
     * Moves the internal iterator position to the next element and returns this element.
     *
     * @return mixed
     */
    public function next();

    /**
     * Tests for the existence of an element that satisfies the given predicate.
     *
     * @param callable $callable The predicate.
     *
     * @return bool TRUE if the predicate is TRUE for at least one element, FALSE otherwise.
     */
    public function exists(callable $callable) : bool;

    /**
     * Returns all the elements of this collection that satisfy the callable $callable.
     * The order of the elements is preserved.
     *
     * @param callable $callable The predicate used for filtering.
     *
     * @return $this A collection with the results of the filter operation.
     */
    public function filter(callable $callable) : self ;

    /**
     * Tests whether the given callable $callable holds for all elements of this collection.
     *
     * @param callable $callable The predicate.
     *
     * @return bool TRUE, if the predicate yields TRUE for all elements, FALSE otherwise.
     */
    public function forAll(callable $callable) : bool ;

    /**
     * Applies the given function to each element in the collection and returns
     * a new collection with the elements returned by the function.
     *
     * @param callable $callable
     *
     * @return $this
     */
    public function apply(callable $callable) : self ;

    /**
     * Call the given method to each element in the collection and returns
     * a new collection with return values for each call
     *
     * @param string $method
     * @param mixed ...$vars
     *
     * @return $this
     */
    public function call(string $method, ... $vars) : self ;

    /**
     * Partitions this collection in two collections according to a predicate.
     * Keys are preserved in the resulting collections.
     *
     * @param callable $callable $p The predicate on which to partition.
     *
     * @return array An array with two elements. The first element contains the collection
     *               of elements where the predicate returned TRUE, the second element
     *               contains the collection of elements where the predicate returned FALSE.
     */
    public function partition(callable $callable) : array ;

    /**
     * Gets the index/key of a given element. The comparison of two elements is strict,
     * that means not only the value but also the type must match.
     * For objects this means reference equality.
     *
     * @param mixed $element The element to search for.
     *
     * @return int|string|bool The key/index of the element or FALSE if the element was not found.
     */
    public function indexOf($element);

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
     * @return $this
     */
    public function slice($offset, $length = null) : self ;
}
