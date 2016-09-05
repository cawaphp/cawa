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

namespace Cawa\Serializer;

trait ArraySerializable
{
    /**
     * @return array
     */
    public function arraySerialize() : array
    {
        $data = Serializer::serialize($this);

        return $data;
    }

    /**
     * @param array $serialized
     */
    public function arrayUnserialize(array $serialized)
    {
        Serializer::unserialize($this, $serialized);
    }
}
