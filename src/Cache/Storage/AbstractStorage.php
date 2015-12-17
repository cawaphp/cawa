<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Cawa\Cache\Storage;

abstract class AbstractStorage
{
    /**
     * Is this storage need to be prefix with version
     *
     * @return bool
     */
    abstract public function isVersionPrefix() : bool;

    /**
     * Get an item from the cache.
     *
     * @param string $key
     *
     * @return bool|mixed
     */
    abstract public function get(string $key);

    /**
     * Get multiple item from the cache.
     * Result will be associative array with key => $value
     * $value is false if the item doesn't exists
     *
     * @param array $keys
     *
     * @return array
     */
    abstract public function multiget(array $keys) : array;

    /**
     * Store an item in the cache.
     *
     * @param string $key final keys
     * @param string $value serialize values
     * @param int|null $ttl in seconds
     *
     * @return bool
     */
    abstract public function set(string $key, string $value, int $ttl = null) : bool;

    /**
     * Store multiple item from the cache.
     * Result will be associative array with key => $result
     * $result is true or false
     *
     * @param array $keys
     * @param int $ttl
     *
     * @return array
     */
    abstract public function multiset(array $keys, int $ttl = null) : array;

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    abstract public function delete(string $key) : bool;

    /**
     * Remove a list of item from the cache.
     *
     * @param array $keys
     *
     * @return int
     */
    abstract public function multidelete(array $keys) : int;

    /**
     * Remove all items from the cache.
     *
     * @param string $prefix
     * @param int $prefixId
     *
     * @return int the new version prefix
     */
    abstract public function flush(string $prefix, int $prefixId = null) : int;

    /**
     * Flush all data of current storage regardless of prefix
     *
     * @return bool
     */
    abstract public function flushAll() : bool;

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  int   $value
     *
     * @return int|bool
     */
    abstract public function increment(string $key, int $value);

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  int   $value
     *
     * @return int|bool
     */
    abstract public function decrement(string $key, int $value);
}
