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

use ReflectionClass;

/**
 * Simple Wrapper due to a php bug
 *
 * @see http://stackoverflow.com/questions/24476185/writing-to-dateperiod-properties-is-unsupported
 */
class DatePeriod implements \Iterator
{
    /**
     * @var \DatePeriod
     */
    private $period;

    /**
     * @var DateTime[]
     */
    private $periods = [];

    /**
     * @return DateTime[]
     */
    private function getPeriods()
    {
        if (!$this->periods) {
            foreach ($this->period as $datetime) {
                $this->periods[] = new DateTime($datetime);
            }
        }

        return $this->periods;
    }

    /**
     * @inheritdoc
     */
    public function __construct(...$params)
    {
        $reflection_class = new ReflectionClass('DatePeriod');
        $this->period = $reflection_class->newInstanceArgs($params);

    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return current($this->getPeriods());
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->getPeriods();
        next($this->periods);
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return key($this->getPeriods());
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return key($this->getPeriods()) !== null;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->getPeriods();
        reset($this->periods);
    }
}
