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

namespace Cawa\VarDumper\Caster;

use Symfony\Component\VarDumper\Caster\Caster;

class Collection
{
    /**
     * @param \Cawa\Orm\Collection $item
     * @param array $a
     *
     * @return array
     */
    public static function cast(\Cawa\Orm\Collection $item, array $a)
    {
        return [
            Caster::PREFIX_VIRTUAL . 'count' => $item->count(),
        ] + $a;
    }
}
