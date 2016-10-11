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

namespace Cawa\VarDumper\Caster;

use Cawa\Orm\CollectionModel as Base;
use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Cloner\Stub;

class CollectionModel
{
    /**
     * @param Base $model
     * @param array $a
     * @param Stub $stub
     * @param bool $isNested
     *
     * @return array
     */
    public static function cast(Base $model, array $a, Stub $stub, bool $isNested)
    {
        if ($isNested) {
            $a = Caster::filter($a, Caster::EXCLUDE_VERBOSE, [
                "\0" . Base::class . "\0" . 'added',
                "\0" . Base::class . "\0" . 'removed',
            ]);
        }

        return $a;
    }
}
