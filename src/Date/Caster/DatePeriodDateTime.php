<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Cawa\Date\Caster;

use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Caster\DateCaster;
use Symfony\Component\VarDumper\Cloner\Stub;

class DatePeriodDateTime
{
    /**
     * @param \Cawa\Date\DatePeriodDateTime $datePeriodDateTime
     * @param array $a
     * @param Stub $stub
     * @param bool $isNested
     * @param int $filter
     *
     * @return array
     */
    public static function cast(\Cawa\Date\DatePeriodDateTime $datePeriodDateTime, array $a, Stub $stub, $isNested, $filter)
    {
        $prefix = Caster::PREFIX_VIRTUAL;

        $start = DateCaster::castDateTime($datePeriodDateTime->getStartDate(), $a, $stub, $isNested, $filter);
        $end = DateCaster::castDateTime($datePeriodDateTime->getEndDate(), $a, $stub, $isNested, $filter);

        $return = [
            $prefix . 'start' => array_pop($start),
            $prefix . 'end' => array_pop($end),
            $prefix . 'duration' => $datePeriodDateTime->getEndDate()->diffForHumans($datePeriodDateTime->getStartDate(), true)
        ];

        $stub->class = get_class($datePeriodDateTime) . ' ' .
            $datePeriodDateTime->getStartDate()->format(' @U') . ' >' .
            $datePeriodDateTime->getEndDate()->format(' @U');

        return $return;
    }
}
