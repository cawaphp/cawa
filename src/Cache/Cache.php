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

namespace Cawa\Cache;

use Cawa\Cache\Storage\AbstractStorage;
use Cawa\Core\App;
use Cawa\Events\TimerEvent;

class Cache
{
    /**
     * @var AbstractStorage
     */
    private $storage;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var int
     */
    private $prefixId;

    /**
     * @var int
     */
    private $ttl;

    /**
     *
     */
    private function __construct()
    {
    }

    /**
     * @param array $config
     *
     * @return static
     */
    public static function create($config) : self
    {
        $cache = new static();

        if (!isset($config['type'])) {
            throw new \InvalidArgumentException('Missing type');
        } else {
            $storageClass = explode('\\', get_class());
            array_pop($storageClass);
            $storageClass[] = 'Storage';
            $storageClass[] = $config['type'];
            $storageClass = implode('\\', $storageClass);
            $cache->type = $config['type'];
        }

        if (!isset($config['prefix'])) {
            throw new \InvalidArgumentException('Missing prefix');
        } else {
            $cache->prefix = $config['prefix'];
        }

        if (isset($config['ttl'])) {
            if (!is_int($config['ttl'])) {
                throw new \InvalidArgumentException(sprintf("Invalid ttl '%s'", $config['ttl']));
            }

            $cache->ttl = $config['ttl'];
        }

        /** @var AbstractStorage $storage */
        $storage = new $storageClass($config['config'] ?? null);
        $cache->storage = $storage;

        if ($storage->isVersionPrefix() == true) {
            $cache->prefixId = (int) $storage->get($cache->prefix);
            if (!$cache->prefixId) {
                $storage->increment($cache->prefix, 1);
                $cache->prefixId = 1;
            }
        }

        return $cache;
    }

    /**
     * @param string $method
     * @param string|array $key
     *
     * @return TimerEvent
     */
    public function getTimerEvent(string $method, $key = null) : TimerEvent
    {
        $event = new TimerEvent('cache.' .  $this->type);

        $data = [
            'method' => substr(strrchr($method, ':'), 1),
            'prefix' => $this->prefix . ($this->prefixId ? '@' . $this->prefixId : ''),
            'key' => null,
            'size' => 0,
        ];

        if ($key) {
            $data['key'] = is_array($key) ? $key : $key;
        }

        $event->addData($data);

        return $event;
    }

    /**
     * @param string $key
     * @param bool $isTag
     *
     * @return string
     */
    protected function getFinalKey($key, bool $isTag = false) : string
    {
        return $this->prefix .
            ($this->prefixId ? '@' . $this->prefixId : '') .
            ':' .
            ($isTag ? '#' : '') .
            $key;
    }

    /**
     * @var callable
     */
    protected static $serialize = [__CLASS__, 'defaultSerialize'];

    /**
     * @param mixed $data
     *
     * @return string
     */
    protected static function serialize($data) : string
    {
        return call_user_func(self::$serialize, $data);
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    protected static function defaultSerialize($data) : string
    {
        $value = serialize($data);
        $compress = gzcompress($value, 1);
        if (strlen($compress) > strlen($value)) {
            return $value;
        } else {
            return $compress;
        }
    }

    /**
     * @var callable
     */
    protected static $unserialize = [__CLASS__, 'defaultUnserialize'];

    /**
     * @param string $data
     *
     * @return mixed
     */
    protected static function unserialize(string $data)
    {
        return call_user_func(self::$unserialize, $data);
    }

    /**
     * @param string $data
     *
     * @return mixed
     */
    protected static function defaultUnserialize(string $data)
    {
        $value = @gzuncompress($data);

        return !$value ? unserialize($data) : unserialize($value);
    }

    /**
     * Get an item from the cache.
     *
     * @param string $key
     * @param callable $callable
     * @param int|null $ttl in seconds
     * @param array $tags list of tags associated to this keys
     *
     * @return bool|mixed
     */
    public function get(string $key, callable $callable = null, int $ttl = null, array $tags = [])
    {
        $event = $this->getTimerEvent(__METHOD__, $key);

        $value = $this->storage->get($this->getFinalKey($key));

        if ($value) {
            $event->addData(['size' => strlen($value)]);
            $value = self::unserialize($value);
        }

        App::events()->emit($event);

        if (!$value && $callable) {
            $value = $callable();
            $this->set($key, $value, $ttl, $tags);
        }

        return $value;
    }

    /**
     * Get multiple item from the cache.
     * Result will be associative array with key => $value
     * $value is false if the item doesn't exists
     *
     * @param array $keys
     *
     * @return array
     */
    public function multiget(array $keys) : array
    {
        $event = $this->getTimerEvent(__METHOD__, $keys);

        $multiKeys = [];
        foreach ($keys as $index => $key) {
            $multiKeys[$index] = $this->getFinalKey($key);
        }

        $multi = $this->storage->multiget($multiKeys);

        $size = 0;
        $keys = array_flip($keys);

        foreach ($keys as $key => $value) {
            if (isset($multi[$this->getFinalKey($key)])) {
                $value = $multi[$this->getFinalKey($key)];
                if (!is_bool($value)) {
                    $size += strlen($value);
                    $keys[$key] = self::unserialize($value);
                } else {
                    $keys[$key] = false;
                }
            } else {
                $keys[$key] = false;
            }
        }

        $event->addData(['size' => $size]);

        App::events()->emit($event);

        return $keys;
    }

    /**
     * Store an item in the cache.
     *
     * @param string $key final keys
     * @param mixed $value serialize values
     * @param int|null $ttl in seconds
     * @param array $tags list of tags associated to this keys
     *
     * @return bool
     */
    public function set(string $key, $value, int $ttl = null, array $tags = []) : bool
    {
        $event = $this->getTimerEvent(__METHOD__, $key);

        $value = self::serialize($value);
        $event->addData(['size' => strlen($value)]);

        $value = $this->storage->set($this->getFinalKey($key), $value, min($ttl, $this->ttl));

        App::events()->emit($event);

        if ($tags) {
            $this->saveTags($key, $tags);
        }

        return $value;
    }

    /**
     * Store multiple item from the cache.
     * Result will be associative array with key => $result
     * $result is true or false
     *
     * @param array $keys
     * @param int $ttl
     * @param array $tags list of tags associated to this keys
     *
     * @return array
     */
    public function multiset(array $keys, int $ttl = null, array $tags = []) : array
    {
        $event = $this->getTimerEvent(__METHOD__, array_keys($keys));

        $multiKeys = [];
        $size = 0;
        foreach ($keys as $key => $value) {
            $serialize = self::serialize($value);
            $size += strlen($serialize);
            $multiKeys[$this->getFinalKey($key)] = $serialize;
        }

        $event->addData(['size' => $size]);

        $multi = $this->storage->multiset($multiKeys, min($ttl, $this->ttl));

        foreach ($keys as $key => $value) {
            $keys[$key] = $multi[$this->getFinalKey($key)];
        }

        App::events()->emit($event);

        if ($tags) {
            foreach ($keys as $key => $value) {
                $this->saveTags($key, $tags);
            }
        }

        return $keys;
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key) : bool
    {
        $event = $this->getTimerEvent(__METHOD__, $key);

        $value = $this->storage->delete($this->getFinalKey($key));

        $event->addData(['size' => $value ? 1 : 0]);
        App::events()->emit($event);

        return $value;
    }

    /**
     * Remove a list of item from the cache.
     *
     * @param array $keys
     *
     * @return int
     */
    public function multidelete(array $keys) : int
    {
        $event = $this->getTimerEvent(__METHOD__, array_keys($keys));

        foreach ($keys as $index => $key) {
            $keys[$index] = $this->getFinalKey($key);
        }

        $count = $this->storage->multidelete($keys);

        $event->addData(['size' => $count]);
        App::events()->emit($event);

        return $count;
    }

    /**
     * Remove all associated keys with a tag
     *
     * @param string $tag
     *
     * @return int
     */
    public function deleteKeysByTag(string $tag) : int
    {
        $event = $this->getTimerEvent(__METHOD__, $tag);
        $tagKey = $this->getFinalKey($tag, true);
        $keys = [];

        $value = $this->storage->get($tagKey);

        if ($value) {
            $keys = self::unserialize($value);
            foreach ($keys as $index => $key) {
                $keys[$index] = $this->getFinalKey($key);
            }
        }

        $keys[] = $tagKey;

        $count = $this->storage->multidelete($keys);

        $event->addData(['size' => $count]);
        App::events()->emit($event);

        return $count;
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush() : bool
    {
        $event = $this->getTimerEvent(__METHOD__);

        $this->storage->flush($this->prefix, $this->prefixId);

        App::events()->emit($event);

        return true;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  int   $value
     *
     * @return int|bool
     */
    public function increment(string $key, int $value)
    {
        $event = $this->getTimerEvent(__METHOD__, $key);

        $value = $this->storage->increment($this->getFinalKey($key), $value);

        App::events()->emit($event);

        return $value;
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  int   $value
     *
     * @return int|bool
     */
    public function decrement(string $key, int $value)
    {
        $event = $this->getTimerEvent(__METHOD__, $key);

        $value = $this->storage->decrement($this->getFinalKey($key), $value);

        App::events()->emit($event);

        return $value;
    }

    /**
     * @param string $key
     * @param array $tags
     */
    private function saveTags(string $key, array $tags)
    {
        $event = $this->getTimerEvent(__METHOD__, $key);
        $event->addData(['key' => [$key => $tags]]);

        $size = 0;
        foreach ($tags as $tag) {
            $tagKey = $this->getFinalKey($tag, true);
            $keys = [];

            $value = $this->storage->get($tagKey);

            if ($value) {
                $size += strlen($value);
                $keys = self::unserialize($value);
            }

            if (array_search($key, $keys) === false) {
                $keys[] = $key;
            }

            $keys = self::serialize($keys);
            $size += strlen($keys);

            $this->storage->set($tagKey, $keys);
        }

        $event->addData(['size' => $size]);

        App::events()->emit($event);
    }
}
