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

namespace Cawa\Controller;

use ReflectionClass;

abstract class ViewController extends AbstractController
{
    /**
     * @param array ...$args
     *
     * @return static
     */
    public static function create(... $args) : self
    {
        $class = static::class;

        return (new ReflectionClass($class))->newInstanceArgs($args);
    }

    /**
     * @return string|array
     */
    abstract public function render();
}
