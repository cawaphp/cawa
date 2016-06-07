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
use Symfony\Component\VarDumper\Cloner\Stub;

class DateTime
{
    public static function cast(\DateTime $dateTime, array $a, Stub $stub, bool $isNested)
    {
        $clone = clone $dateTime;
        $clone->setTimezone(\Cawa\Date\DateTime::getUserTimezone());
        $date = strftime('%x %X', strtotime($clone->format('Y-m-d H:i:s')));

        unset($a[Caster::PREFIX_DYNAMIC . 'timezone_type']);

        return [
            Caster::PREFIX_DYNAMIC . 'date' => $date,
            Caster::PREFIX_DYNAMIC . 'timezone' => $dateTime->format('T'),
            Caster::PREFIX_DYNAMIC . 'userTimezone' => $clone->format('T'),
        ] + $a;
    }
}
