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

namespace Cawa\Events;

class ManualTimerEvent extends TimerEvent
{
    /**
     * @param float $start
     *
     * @return $this|self
     */
    public function setStart(float $start) : self
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @param float $duration
     *
     * @return $this|self
     */
    public function setDuration(float $duration) : self
    {
        $this->duration = $duration;

        return $this;
    }
}
