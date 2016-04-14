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

namespace Cawa\Events;

class TimerEvent extends Event
{
    /**
     * @param string $name
     * @param array $data
     */
    public function __construct(string $name, array $data = [])
    {
        $this->start = microtime(true);
        parent::__construct($name, $data);
    }

    /**
     * start time of the events (in microseconds)
     *
     * @var float
     */
    protected $start;

    /**
     * start time of the events (in microseconds)
     *
     * @return float
     */
    public function getStart() : float
    {
        return $this->start;
    }

    /**
     * @var float
     */
    protected $duration;

    /**
     * @return float
     */
    public function getDuration() : float
    {
        return $this->duration;
    }

    /**
     * Save duration of this events
     *
     * @return void
     */
    public function onEmit()
    {
        if (!$this->duration) {
            $time = microtime(true);
            $this->duration = round($time - $this->start, 6);
        }
    }
}
