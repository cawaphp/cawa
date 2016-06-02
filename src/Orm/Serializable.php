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

abstract class Serializable implements \Serializable
{
    use SerializableTrait;

    /**
     * @return string
     */
    public function serialize() : string
    {
        $data = self::getSerializableData($this);

        return serialize($data);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        self::unserializeData($this, $data);
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

        $data = self::getSerializableData($this);
        $data["@type"] = $destination;

        /** @var Serializable $return */
        $reflection = new \ReflectionClass($destination);
        $return = $reflection->newInstanceWithoutConstructor();
        self::unserializeData($return, $data);

        return $return;
    }
}
