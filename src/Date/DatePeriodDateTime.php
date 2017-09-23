<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Cawa\Date;

class DatePeriodDateTime extends DateTime
{
    /**
     * @param DateTime $start
     * @param DateTime $end
     */
    public function __construct(DateTime $start, DateTime $end)
    {
        if ($end->lt($start)) {
            throw new \LogicException(sprintf(
                "start '%s' must be lower than end '%s'",
                $start->format(),
                $end->format()
            ));
        }

        parent::__construct($start);
        $this->startDate = $start;
        $this->endDate = $end;
    }

    /**
     * @var DateTime
     */
    private $startDate;

    /**
     * @return DateTime
     */
    public function getStartDate() : DateTime
    {
        return $this->startDate;
    }

    /**
     * @var DateTime
     */
    private $endDate;

    /**
     * @return DateTime
     */
    public function getEndDate() : DateTime
    {
        return $this->endDate;
    }

    public function __toString()
    {
        return $this->format();
    }

    /**
     * @param self $comparison
     * @param bool $equal
     *
     * @return bool
     */
    public function contains(DatePeriodDateTime $comparison, bool $equal = true) : bool
    {
        if ($equal) {
            return $comparison->gte($this) && $comparison->getEndDate()->lte($this->endDate);
        } else {
            return $comparison->gt($this) && $comparison->getEndDate()->lt($this->endDate);
        }
    }

    /**
     * @param self $comparison
     *
     * @return bool
     */
    public function overlap(DatePeriodDateTime $comparison) : bool
    {
        $start = $this;
        $end = $this->getEndDate();

        $comparisonStart = $comparison;
        $comparisonEnd = $comparison->getEndDate();

        /*
         *            L     H
         * C :        |_____|
         * 1 :      __|__   |
         * 2 :     ___|     |
         * 3 :        |   __|___
         * 4 :        |     |___
         * 5 :      __|_____|__
         * 6 :        |_____|___
         * 7 :      __|_____|
         * 8 :        |_____|
         * 9 :   __   |     |
         * 10:        |     | __
         * 11:        |  __ |
         */

        // case 1
        if ($start->lt($comparisonStart) && $comparisonStart->gt($start) && $end->lt($comparisonEnd) && $end->gt($comparisonStart)) {
            return true;
        }

        // case 2
        if ($end->eq($comparisonStart)) {
            return false;
        }

        // case 3
        if ($start->gt($comparisonStart) && $start->lt($comparisonEnd) && $end->gt($comparisonEnd)) {
            return true;
        }

        // case 4
        if ($start->eq($comparisonEnd)) {
            return false;
        }

        // case 5
        if ($start->lt($comparisonStart) && $end->gt($comparisonEnd)) {
            return true;
        }

        // case 6
        if ($start->eq($comparisonStart) && $end->gt($comparisonEnd)) {
            return true;
        }

        // case 7
        if ($start->lt($comparisonStart) && $end->eq($comparisonEnd)) {
            return true;
        }

        // case 8
        if ($start->eq($comparisonStart) && $end->eq($comparisonEnd)) {
            return true;
        }

        // case 11
        if ($start->gt($comparisonStart) && $end->lt($comparisonEnd)) {
            return true;
        }

        return false;
    }


    /**
     * @param self $comparison
     *
     * @return DatePeriodDateTime[]
     */
    public function substract(DatePeriodDateTime $comparison) : array
    {
        $start = $this;
        $end = $this->getEndDate();

        $comparisonStart = $comparison;
        $comparisonEnd = $comparison->getEndDate();

        /*
         *            L     H
         * C :        |_____|
         * 1 :      __|__   |
         * 2 :     ___|     |
         * 3 :        |   __|___
         * 4 :        |     |___
         * 5 :      __|_____|__
         * 6 :        |_____|___
         * 7 :      __|_____|
         * 8 :        |_____|
         * 9 :   __   |     |
         * 10:        |     | __
         * 11:        |  __ |
         */

        // case 1
        if ($start->lt($comparisonStart) && $comparisonStart->gt($start) && $end->lt($comparisonEnd) && $end->gt($comparisonStart)) {
            return [new static($start, $comparisonStart)];
        }

        // case 2
        if ($end->eq($comparisonStart)) {
            return [$this];
        }

        // case 3
        if ($start->gt($comparisonStart) && $start->lt($comparisonEnd) && $end->gt($comparisonEnd)) {
            return [new static($comparisonEnd, $end)];
        }

        // case 4
        if ($start->eq($comparisonEnd)) {
            return [$this];
        }

        // case 5
        if ($start->lt($comparisonStart) && $end->gt($comparisonEnd)) {
            return [new static($start, $comparisonStart), new static($comparisonEnd, $end)];
        }

        // case 6
        if ($start->eq($comparisonStart) && $end->gt($comparisonEnd)) {
            return [new static($comparisonEnd, $end)];
        }

        // case 7
        if ($start->lt($comparisonStart) && $end->eq($comparisonEnd)) {
            return [new static($start, $comparisonStart)];
        }

        // case 8
        if ($start->eq($comparisonStart) && $end->eq($comparisonEnd)) {
            return [];
        }

        // case 11
        if ($start->gt($comparisonStart) && $end->lt($comparisonEnd)) {
            return [];
        }

        return [$this];
    }
}
