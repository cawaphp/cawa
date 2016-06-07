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
    public static function cast(\DateTime $dateTime)
    {
        $clone = clone $dateTime;
        $clone->setTimezone(\Cawa\Date\DateTime::getUserTimezone());
        $date = strftime('%x %X', strtotime($clone->format('Y-m-d H:i:s')));

        return [
            Caster::PREFIX_VIRTUAL . 'date' => $date,
            Caster::PREFIX_VIRTUAL . 'timezone' => $dateTime->format('T'),
            Caster::PREFIX_VIRTUAL . 'userTimezone' => $clone->format('T')
        ];
    }
}
