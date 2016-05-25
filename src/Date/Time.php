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

        return $clone->format('%X');
    }
}
