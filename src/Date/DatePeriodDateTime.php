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

class DatePeriodDateTime extends DateTime
{

    /**
     * @param DateTime $start
     * @param DateTime $end
     */
    public function __construct(DateTime $start, DateTime $end)
    {
        parent::__construct($start);
        $this->endDate = $end;
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
}
