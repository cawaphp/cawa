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

trait JsonSerializable
{
    /**
     * @return array
     */
    public function jsonSerialize() : array
    {
        $data = Serializer::serialize($this);

        return $data;
    }

    /**
     * @param string $serialized
     */
    public function jsonUnserialize(string $serialized)
    {
        Serializer::unserialize($this, json_decode($serialized, true));
    }
}
