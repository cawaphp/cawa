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

class DateTime
{
    /**
     * @param \DateTime $dateTime
     * @param array $a
     *
     * @return array
     */
    public static function cast(\DateTime $dateTime, array $a)
    {
        $clone = clone $dateTime;
        $clone->setTimezone(\Cawa\Date\DateTime::getUserTimezone());
        $date = strftime('%x %X', strtotime($clone->format('Y-m-d H:i:s')));
        $dateInternal = strftime('%x %X', strtotime($dateTime->format('Y-m-d H:i:s')));

        unset($a[Caster::PREFIX_DYNAMIC . 'timezone_type']);

        return [
            Caster::PREFIX_VIRTUAL . 'userDate' => $date,
            Caster::PREFIX_VIRTUAL . 'userTimezone' => $clone->format('T'),
            Caster::PREFIX_DYNAMIC . 'date' => $dateInternal,
        ] + $a;
    }
}
