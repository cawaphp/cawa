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
    protected function getPeriods()
    {
        if (!$this->periods) {

            foreach ($this->period as $datetime) {
                $start = new DateTime($datetime);
                $end = (clone $start)->add($this->period->getDateInterval());
                if ($end->getTimestamp() > $this->period->getEndDate()->getTimestamp()) {
                    $end = new DateTime($this->period->getEndDate());
                }

                $this->periods[] = new DatePeriodDateTime($start, $end);
            }
        }

        return $this->periods;
    }

    /**
     * @inheritdoc
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

        // we had 1 second in order to include end date on iterator
        if ($params[2] instanceof \DateTime) {
            $params[2] = $params[2]->add(new \DateInterval('PT1S'));
        }

        $reflection_class = new ReflectionClass('DatePeriod');
        $this->period = $reflection_class->newInstanceArgs($params);
        $this->periods = [];
    }

    /**
     * @param array ...$args The same args used for new instasnce
     *
     * @return DatePeriod
     */
    public function changePeriod(...$args) : self
    {
        $this->initPeriod(...$args);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStartDate() : DateTime
    {
        return new DateTime($this->period->getStartDate());
    }

    /**
     * @inheritdoc
     */
    public function getEndDate() : DateTime
    {
        return (new DateTime($this->period->getEndDate()))->addSecond(-1);
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
