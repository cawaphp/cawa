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

use Cawa\Orm\Model as ModelBase;
use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Cloner\Stub;

class Model
{
    /**
     * @param ModelBase $model
     * @param array $a
     * @param Stub $stub
     * @param bool $isNested
     *
     * @return array
     */
    public static function cast(ModelBase $model, array $a, Stub $stub, bool $isNested)
    {
        if ($isNested) {
            $a = Caster::filter($a, Caster::EXCLUDE_VERBOSE, [
                "\0" . '*' . "\0" . 'changedProperties',
            ]);
        }

        return $a;
    }
}
