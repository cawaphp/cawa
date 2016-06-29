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

trait Serializable
{
    /**
     * @return string
     */
    public function serialize() : string
    {
        $data = Serializer::serialize($this);

        return serialize($data);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        Serializer::unserialize($this, unserialize($serialized));
    }
}
