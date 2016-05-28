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
        $data = $this->getSerializableData($this);

        return serialize(['d' => $data]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->unserializeData($this, $data['d']);
    }
}
