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

use Symfony\Component\VarDumper\Caster\Caster;

class DateInterval
{
    /**
     * @param \DateInterval $dateInterval
     *
     * @return array
     */
    public static function cast(\DateInterval $dateInterval)
    {
        $prefix = Caster::PREFIX_VIRTUAL;

        $return = 'P';

        if ($dateInterval->y) {
            $return .= $dateInterval->y . 'Y';
        }

        if ($dateInterval->m) {
            $return .= $dateInterval->m . 'M';
        }

        if ($dateInterval->d) {
            $return .= $dateInterval->d . 'D';
        }

        if ($dateInterval->h || $dateInterval->i || $dateInterval->s) {
            $return .= 'T';

            if ($dateInterval->h) {
                $return .= $dateInterval->h . 'H';
            }

            if ($dateInterval->i) {
                $return .= $dateInterval->i . 'M';
            }

            if ($dateInterval->s) {
                $return .= $dateInterval->s . 'S';
            }
        }

        return [
            $prefix . 'format' => $return
        ];
    }
}
