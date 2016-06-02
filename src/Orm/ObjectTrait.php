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

use ReflectionClass;

trait ObjectTrait
{
    /**
     * @param array ...$args
     *
     * @return $this
     */
    public static function create(... $args) : self
    {
        $class = static::class;

        return (new ReflectionClass($class))->newInstanceArgs($args);
    }
}
