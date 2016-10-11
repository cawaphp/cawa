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

/**
 * Сáша frameworks tests
 *
 * @author tchiotludo <http://github.com/tchiotludo>
 */
namespace CawaTest\Date;

use Cawa\Date\DatePeriod;
use Cawa\Date\DatePeriodDateTime;
use Cawa\Date\DateTime;
use PHPUnit_Framework_TestCase as TestCase;

class DatePeriodTest extends TestCase
{
    /**
     * Test start and end date
     *
     * @param DateTime $start
     * @param \DateInterval $interval
     * @param DateTime $end
     * @param int $options
     *
     * @dataProvider periodProvider
     */
    public function testStartEndDate(DateTime $start, \DateInterval $interval, DateTime $end, int $options = null)
    {
        $period = new DatePeriod($start, $interval, $end, $options);
        $this->assertEquals($period->getStartDate()->format(), $start->format());
        $this->assertEquals($period->getEndDate()->format(), $end->format());
    }

    /**
     * Test start and end date
     *
     * @param DateTime $start
     * @param \DateInterval $interval
     * @param DateTime $end
     * @param int $options
     * @param array $expected
     *
     * @dataProvider periodProvider
     */
    public function testPeriod(
        DateTime $start,
        \DateInterval $interval,
        DateTime $end,
        int $options = null,
        array $expected
    ) {
        $period = new DatePeriod($start, $interval, $end, $options);

        $startDates = $endDates = [];
        /** @var DatePeriodDateTime $item */
        foreach ($period as $item) {
            $startDates[] = $item->format();
            $endDates[] = $item->getEndDate()->format();
        }

        $this->assertEquals($expected['start'], $startDates);
        $this->assertEquals($expected['end'], $endDates);
    }

    /**
     * @return array
     */
    public function periodProvider() : array
    {
        return [
            [
                new DateTime('2016-01-01 00:00:00'),
                new \DateInterval('P7D'),
                new DateTime('2016-01-01 00:00:00'),
                null,
                [
                    'start' => [
                    ],
                    'end' => [
                    ]
                ],
            ],
            [
                new DateTime('2016-01-01 00:00:00'),
                new \DateInterval('P7D'),
                new DateTime('2016-01-07 00:00:00'),
                null,
                [
                    'start' => [
                        '2016-01-01 00:00:00'
                    ],
                    'end' => [
                        '2016-01-07 00:00:00'
                    ]
                ],
            ],
            [
                new DateTime('2016-01-01 00:00:00'),
                new \DateInterval('P7D'),
                new DateTime('2016-01-08 00:00:00'),
                null,
                [
                    'start' => [
                        '2016-01-01 00:00:00',
                    ],
                    'end' => [
                        '2016-01-08 00:00:00',
                    ]
                ],
            ],
            [
                new DateTime('2016-01-01 00:00:00'),
                new \DateInterval('P7D'),
                new DateTime('2016-01-09 00:00:00'),
                null,
                [
                    'start' => [
                        '2016-01-01 00:00:00',
                        '2016-01-08 00:00:00'
                    ],
                    'end' => [
                        '2016-01-08 00:00:00',
                        '2016-01-09 00:00:00'
                    ]
                ],
            ],
            [
                new DateTime('2016-01-01 00:00:00'),
                new \DateInterval('P1D'),
                new DateTime('2016-01-07 00:00:00'),
                null,
                [
                    'start' => [
                        '2016-01-01 00:00:00',
                        '2016-01-02 00:00:00',
                        '2016-01-03 00:00:00',
                        '2016-01-04 00:00:00',
                        '2016-01-05 00:00:00',
                        '2016-01-06 00:00:00',
                    ],
                    'end' => [
                        '2016-01-02 00:00:00',
                        '2016-01-03 00:00:00',
                        '2016-01-04 00:00:00',
                        '2016-01-05 00:00:00',
                        '2016-01-06 00:00:00',
                        '2016-01-07 00:00:00',
                    ]
                ],
            ],
            [
                new DateTime('2016-01-01 00:00:00'),
                new \DateInterval('P1D'),
                new DateTime('2016-01-07 00:00:00'),
                \DatePeriod::EXCLUDE_START_DATE,
                [
                    'start' => [
                        '2016-01-02 00:00:00',
                        '2016-01-03 00:00:00',
                        '2016-01-04 00:00:00',
                        '2016-01-05 00:00:00',
                        '2016-01-06 00:00:00',
                    ],
                    'end' => [
                        '2016-01-03 00:00:00',
                        '2016-01-04 00:00:00',
                        '2016-01-05 00:00:00',
                        '2016-01-06 00:00:00',
                        '2016-01-07 00:00:00',
                    ]
                ],
            ],
            [
                new DateTime('2016-01-01 00:00:00'),
                new \DateInterval('P1D'),
                new DateTime('2016-01-07 00:00:00'),
                DatePeriod::EXCLUDE_END_DATE,
                [
                    'start' => [
                        '2016-01-01 00:00:00',
                        '2016-01-02 00:00:00',
                        '2016-01-03 00:00:00',
                        '2016-01-04 00:00:00',
                        '2016-01-05 00:00:00',
                    ],
                    'end' => [
                        '2016-01-02 00:00:00',
                        '2016-01-03 00:00:00',
                        '2016-01-04 00:00:00',
                        '2016-01-05 00:00:00',
                        '2016-01-06 00:00:00',
                    ]
                ],
            ],
            [
                new DateTime('2016-01-01 00:00:00'),
                new \DateInterval('P1D'),
                new DateTime('2016-01-07 00:00:00'),
                DatePeriod::EXCLUDE_END_DATE | \DatePeriod::EXCLUDE_START_DATE,
                [
                    'start' => [
                        '2016-01-02 00:00:00',
                        '2016-01-03 00:00:00',
                        '2016-01-04 00:00:00',
                        '2016-01-05 00:00:00',
                    ],
                    'end' => [
                        '2016-01-03 00:00:00',
                        '2016-01-04 00:00:00',
                        '2016-01-05 00:00:00',
                        '2016-01-06 00:00:00',
                    ]
                ],
            ],
        ];
    }
}
