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

namespace Cawa\Date;

use Punic\Data;
use Punic\Misc;
use Punic\Unit;

class Time extends DateTime
{
    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->format('H:i:s');
    }

    /**
     * {@inheritdoc}
     */
    public function format($format = null)
    {
        if (is_null($format)) {
            $format = 'H:i:s';
        }

        return parent::format($format);
    }

    /**
     * @param bool $day
     * @param bool $hour
     *
     * @return string
     */
    public function display(bool $day = true, bool $hour = true) : string
    {
        $clone = clone $this;
        $clone->setTimezone(self::getUserTimezone());

        $format = [];

        if ($this->hour) {
            $format[] = Unit::format($this->hour, "duration/hour");
        }

        if ($this->minute) {
            $format[] = Unit::format($this->minute, "duration/minute");
        }

        if ($this->second) {
            $format[] = Unit::format($this->second, "duration/second");
        }

        return Misc::joinUnits($format, 'narrow');
    }
}
