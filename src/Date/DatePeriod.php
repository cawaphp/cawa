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

use ReflectionClass;
use Traversable;

/**
 * Simple Wrapper due to a php bug
 *
 * @see http://stackoverflow.com/questions/24476185/writing-to-dateperiod-properties-is-unsupported
 */
class DatePeriod implements \IteratorAggregate
{
    const EXCLUDE_END_DATE = 2;

    /**
     * @var \DatePeriod
     */
    protected $period;

    /**
     * @var DatePeriodDateTime[]
     */
    protected $periods = [];

    /**
     * @var bool
     */
    protected $includeEndDate = true;

    /**
     * @return DatePeriodDateTime[]
     */
    protected function getPeriods()
    {
        if (!$this->periods) {
            foreach ($this->period as $datetime) {
                $start = new DateTime($datetime);
                $end = $start->add($this->period->getDateInterval());

                // we truncate end date to end date of period if needed
                if ($end->getTimestamp() > $this->period->getEndDate()->getTimestamp()) {
                    $end = new DateTime($this->period->getEndDate());
                }

                $this->periods[] = new DatePeriodDateTime($start, $end);
            }

            // we remove end date if needed
            if ($this->includeEndDate == false &&
                $this->periods[sizeof($this->periods) - 1]->getEndDate()->getTimestamp() ==
                    $this->period->getEndDate()->getTimestamp()
            ) {
                array_pop($this->periods);
            }
        }

        return $this->periods;
    }

    /**
     * {@inheritdoc}
     */
    public function __construct(...$args)
    {
        $this->initPeriod(...$args);
    }

    /**
     *
     */
    private function initPeriod()
    {
        $params = func_get_args();

        if (isset($params[3]) && ($params[3] & self::EXCLUDE_END_DATE)) {
            $this->includeEndDate = false;
        }

        $reflection_class = new ReflectionClass('DatePeriod');
        $this->period = $reflection_class->newInstanceArgs($params);
        $this->periods = [];
    }

    /**
     * @param array ...$args The same args used for new instasnce
     *
     * @return $this|self
     */
    public function changePeriod(...$args) : self
    {
        $this->initPeriod(...$args);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartDate() : DateTime
    {
        return new DateTime($this->period->getStartDate());
    }

    /**
     * {@inheritdoc}
     */
    public function getEndDate() : DateTime
    {
        return (new DateTime($this->period->getEndDate()));
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->getPeriods());
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->getPeriods();
        next($this->periods);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->getPeriods());
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return key($this->getPeriods()) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->getPeriods();
        reset($this->periods);
    }

    /**
     * @return \ArrayIterator|Traversable|DatePeriodDateTime[]
     */
    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator($this->getPeriods());
    }
}
