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
use ReflectionObject;

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

    /**
     * @param string $destination
     *
     * @return object
     */
    public function cast(string $destination)
    {
        if (!is_subclass_of($destination, get_class($this))) {
            throw new \InvalidArgumentException(sprintf(
                '%s is not a descendant of $object class: %s.',
                $destination,
                get_class($this)
            ));
        }

        return unserialize(
            preg_replace(
                '/^C:\d+:"[^"]++"/',
                'C:'.strlen($destination).':"'.$destination.'"',
                serialize($this)
            )
        );
    }
}
