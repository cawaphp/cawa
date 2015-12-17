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

use Cawa\Uri\Uri;

class Redis extends AbstractStorage
{
    /**
     * Indicates is APCu is supported.
     *
     * @var \Redis
     */
    protected $client = false;

    /**
     * @param string|Uri $connectionString
     */
    public function __construct($connectionString)
    {
        if (!$connectionString instanceof Uri) {
            $uri = new Uri($connectionString);
        } else {
            $uri = $connectionString;
        }

        $this->client = new \Redis();
        $this->client->pconnect($uri->getHost(), $uri->getPort(), 2.5);
    }

    /**
     * {@inheritdoc}
     */
    public function isVersionPrefix() : bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key)
    {
        return $this->client->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function multiget(array $keys) : array
    {
        return $this->client->mGet($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function multiset(array $keys, int $ttl = null) : array
    {
        if ($ttl) {
            return $this->client->msetNx($keys, $ttl);
        } else {
            return $this->client->mset($keys);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, string $value, int $ttl = null) : bool
    {
        if ($ttl) {
            return $this->client->setEx($key, $ttl, $value);
        } else {
            return $this->client->set($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key) : bool
    {
        return $this->client->delete($key) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function multidelete(array $keys) : int
    {
        return $this->client->delete($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function flush(string $prefix, int $prefixId = null) : int
    {
        return $this->increment($prefix, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function flushAll() : bool
    {
        return $this->client->flushDb();
    }

    /**
     * {@inheritdoc}
     */
    public function increment(string $key, int $value)
    {
        return $this->client->incrBy($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement(string $key, int $value)
    {
        return $this->client->decrBy($key, $value);
    }
}
