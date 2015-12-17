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

namespace Cawa\Http\SessionStorage;

abstract class AbstractStorage
{
    /**
     * @var callable
     */
    protected static $serialize = 'serialize';

    /**
     * @param array $data
     *
     * @return string
     */
    protected static function serialize(array $data) : string
    {
        return call_user_func(self::$serialize, $data);
    }

    /**
     * @var callable
     */
    protected static $unserialize = 'unserialize';

    /**
     * @param string $data
     *
     * @return array
     */
    protected static function unserialize(string $data) : array
    {
        return call_user_func(self::$unserialize, $data);
    }

    /**
     * @return bool
     */
    abstract public function open() : bool;

    /**
     * return false if the session doesn't exists
     *
     * @param string $id
     *
     * @return array
     */
    abstract public function read(string $id);

    /**
     * @param string $id
     * @param array $data
     * @param int $startTime
     * @param int $accessTime
     *
     * @return bool|int
     */
    abstract public function write(string $id, array $data, int $startTime, int $accessTime);

    /**
     * Increment last access date
     *
     * @param string $id
     * @param array $data
     * @param int $startTime
     * @param int $accessTime
     *
     * @return bool|int
     */
    abstract public function touch(string $id, array $data, int $startTime, int $accessTime);

    /**
     * @param string $id
     *
     * @return bool
     */
    abstract public function destroy(string $id) : bool;
}
