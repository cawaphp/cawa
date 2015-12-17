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

class Apc extends AbstractStorage
{
    /**
     * Indicates is APCu is supported.
     *
     * @var bool
     */
    protected $apcu = false;

    /**
     *
     */
    public function __construct()
    {
        $this->apcu = function_exists('apcu_fetch');
    }

    /**
     * {@inheritdoc}
     */
    public function isVersionPrefix() : bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key)
    {
        return $this->apcu ? apcu_fetch($key) : apc_fetch($key);
    }

    /**
     * {@inheritdoc}
     */
    public function multiget(array $keys) : array
    {
        $return = [];
        foreach ($keys as $key) {
            $return[$key] = $this->get($key);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function multiset(array $keys, int $ttl = null) : array
    {
        $return = [];
        foreach ($keys as $key => $value) {
            $return[$key] = $this->set($key, $value, $ttl);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, string $value, int $ttl = null) : bool
    {
        $ttl = is_null($ttl) ? 0 : $ttl;

        return $this->apcu ? apcu_store($key, $value, $ttl) : apc_store($key, $value, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key) : bool
    {
        return $this->apcu ? apcu_delete($key) : apc_delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function multidelete(array $keys) : int
    {
        $count = 0;
        foreach ($keys as $key) {
            $count = $count + ($this->delete($key) ? 1 : 0);
        }

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(string $prefix, int $prefixId = null) : int
    {
        $regexp = '/' . $prefix . ':/';

        if ($this->apcu) {
            $iterator = new \APCuIterator($regexp, APC_ITER_KEY);
        } else {
            $iterator = new \APCIterator('user', $regexp, APC_ITER_KEY);
        }

        foreach ($iterator as $item) {
            $this->apcu ? apcu_delete($item['key']) : apc_delete($item['key']);
        }

        return $prefixId;
    }

    /**
     * {@inheritdoc}
     */
    public function flushAll() : bool
    {
        return $this->apcu ? apcu_clear_cache() : apc_clear_cache('user');
    }

    /**
     * {@inheritdoc}
     */
    public function increment(string $key, int $value)
    {
        return $this->apcu ? apcu_inc($key, $value) : apc_inc($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement(string $key, int $value)
    {
        return $this->apcu ? apcu_dec($key, $value) : apc_dec($key, $value);
    }
}
