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

class Date extends DateTime
{
    /**
     * 15 hours, 2 minutes
     */
    const DISPLAY_DURATION = 'duration';

    /**
     * 'EEEE, MMMM d, y' - 'Wednesday, August 20, 2014'
     */
    const DISPLAY_FULL = 'medium';

    /**
     * 'MMMM d, y' - 'August 20, 2014'
     */
    const DISPLAY_LONG = 'long';

    /**
     * 'MMM d, y' - 'August 20, 2014'
     */
    const DISPLAY_MEDIUM = 'medium';

    /**
     * 'M/d/yy' - '8/20/14'
     */
    const DISPLAY_SHORT = 'short';

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->format('Y-m-d');
    }

    /**
     * {@inheritdoc}
     */
    public function format($format = null)
    {
        if (is_null($format)) {
            $format = 'Y-m-d';
        }

        return parent::format($format);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function display($type = self::DISPLAY_SHORT) : string
    {
        return Calendar::formatDate($this, $type);
    }
}
