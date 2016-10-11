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

namespace Cawa\Date;

use Punic\Calendar;
use Punic\Misc;
use Punic\Unit;

class Time extends DateTime
{
    /**
     * 15 hours, 2 minutes
     */
    const DISPLAY_DURATION = 'duration';

    /**
     * 'h:mm:ss a zzzz' - '11:42:13 AM GMT+2:00'
     */
    const DISPLAY_FULL = 'medium';

    /**
     * 'h:mm:ss a z' - '11:42:13 AM GMT+2:00'
     */
    const DISPLAY_LONG = 'long';

    /**
     * 'h:mm:ss a' - '11:42:13 AM'
     */
    const DISPLAY_MEDIUM = 'medium';

    /**
     * 'h:mm a' - '11:42 AM'
     */
    const DISPLAY_SHORT = 'short';

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
     * @param int|DateTime $duration if datetime, start date, if int duration in second
     * @param DateTime $end
     *
     * @return $this|self
     */
    public static function createFromDuration($duration, DateTime $end = null) : self
    {
        if ($duration instanceof DateTime && $end) {
            $duration = $end->getTimestamp() - $duration->getTimestamp();
        }

        return static::create(null, null, null, 0, 0, 0)
            ->addSeconds($duration);
    }

    /**
     * @return int
     */
    public function getDuration() : int
    {
        return ($this->hour * 60 * 60) +
            ($this->minute * 60) +
            $this->second;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function display($type = self::DISPLAY_SHORT) : string
    {
        if ($type == self::DISPLAY_DURATION) {
            $format = [];

            if ($this->hour) {
                $format[] = Unit::format($this->hour, 'duration/hour');
            }

            if ($this->minute) {
                $format[] = Unit::format($this->minute, 'duration/minute');
            }

            if ($this->second) {
                $format[] = Unit::format($this->second, 'duration/second');
            }

            return Misc::joinUnits($format, 'narrow');
        }

        return Calendar::formatTime($this, $type);
    }
}
