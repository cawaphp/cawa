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

/**
 * Сáша frameworks tests.
 *
 * @author tchiotludo <http://github.com/tchiotludo>
 */

namespace CawaTest\Date;

use Cawa\Date\DatePeriod;
use Cawa\Date\DatePeriodDateTime;
use Cawa\Date\DateTime;
use PHPUnit\Framework\TestCase;

class DatePeriodTest extends TestCase
{
    /**
     * Test start and end date.
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
     * Test start and end date.
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
                    ],
                ],
            ],
            [
                new DateTime('2016-01-01 00:00:00'),
                new \DateInterval('P7D'),
                new DateTime('2016-01-07 00:00:00'),
                null,
                [
                    'start' => [
                        '2016-01-01 00:00:00',
                    ],
                    'end' => [
                        '2016-01-07 00:00:00',
                    ],
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
                    ],
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
                        '2016-01-08 00:00:00',
                    ],
                    'end' => [
                        '2016-01-08 00:00:00',
                        '2016-01-09 00:00:00',
                    ],
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
                    ],
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
                    ],
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
                    ],
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
                    ],
                ],
            ],
        ];
    }

    /**
     * Test contains method
     *
     * @param array $date
     * @param array $comp
     * @param bool $equal
     * @param bool $result
     *
     * @dataProvider containsProvider
     */
    public function testContains(array $date, array $comp, bool $equal, bool $result)
    {
        $date = new DatePeriodDateTime(new DateTime($date[0]), new DateTime($date[1]));
        $comp = new DatePeriodDateTime(new DateTime($comp[0]), new DateTime($comp[1]));

        $this->assertEquals($result, $date->contains($comp, $equal));
    }

    /**
     * @return array
     */
    public function containsProvider() : array
    {
        return [
            [
                ['2016-01-01 00:00:00', '2016-01-07 00:00:00'],
                ['2016-01-02 00:00:00', '2016-01-03 00:00:00'],
                true,
                true,
            ],
            [
                ['2016-01-01 00:00:00', '2016-01-07 00:00:00'],
                ['2016-01-06 00:00:00', '2016-01-08 00:00:00'],
                true,
                false,
            ],
            [
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                ['2016-01-06 00:00:00', '2016-01-08 00:00:00'],
                true,
                false,
            ],
            [
                ['2016-01-01 00:00:00', '2016-01-07 00:00:00'],
                ['2016-01-01 00:00:00', '2016-01-02 00:00:00'],
                true,
                true,
            ],
            [
                ['2016-01-01 00:00:00', '2016-01-07 00:00:00'],
                ['2016-01-01 00:00:00', '2016-01-02 00:00:00'],
                false,
                false,
            ],
        ];
    }

    /**
     * Test contains method
     *
     * @param array $date
     * @param array $comp
     * @param bool $result
     *
     * @dataProvider overlapProvider
     */
    public function testOverlap(array $date, array $comp, bool $result)
    {
        $date = new DatePeriodDateTime(new DateTime($date[0]), new DateTime($date[1]));
        $comp = new DatePeriodDateTime(new DateTime($comp[0]), new DateTime($comp[1]));

        $this->assertEquals($result, $date->overlap($comp));
    }

    /**
     * @return array
     */
    public function overlapProvider() : array
    {
        return [
            'case 1 - good' => [
                ['2016-01-03 00:00:00', '2016-01-10 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                true,
            ],
            'case 1 - wrong' => [
                ['2016-01-03 00:00:00', '2016-01-06 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                false,
            ],
            'case 2 - good' => [
                ['2016-01-03 00:00:00', '2016-01-07 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                false,
            ],
            'case 3 - good' => [
                ['2016-01-10 00:00:00', '2016-01-15 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                true,
            ],
            'case 3 - wrong' => [
                ['2016-01-15 00:00:00', '2016-01-16 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                false,
            ],
            'case 4 - good' => [
                ['2016-01-14 00:00:00', '2016-01-15 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                false,
            ],
            'case 5 - good' => [
                ['2016-01-06 00:00:00', '2016-01-15 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                true,
            ],
            'case 6 - good' => [
                ['2016-01-07 00:00:00', '2016-01-15 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                true,
            ],
            'case 6 - wrong' => [
                ['2016-01-14 00:00:00', '2016-01-15 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                false,
            ],
            'case 7 - good' => [
                ['2016-01-03 00:00:00', '2016-01-14 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                true,
            ],
            'case 7 - wrong' => [
                ['2016-01-03 00:00:00', '2016-01-07 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                false,
            ],
            'case 8 - good' => [
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                true,
            ],
            'case 9 - good' => [
                ['2016-01-03 00:00:00', '2016-01-04 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                false,
            ],
            'case 10 - good' => [
                ['2016-01-15 00:00:00', '2016-01-16 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                false,
            ],
            'case 11 - good' => [
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                ['2016-01-10 00:00:00', '2016-01-11 00:00:00'],
                true,
            ],
        ];
    }

    /**
     * Test contains method
     *
     * @param array $date
     * @param array $substract
     * @param array $result
     *
     * @dataProvider substractProvider
     */
    public function testSubstract(array $date, array $substract, array $result)
    {
        $date = new DatePeriodDateTime(new DateTime($date[0]), new DateTime($date[1]));
        $substract = new DatePeriodDateTime(new DateTime($substract[0]), new DateTime($substract[1]));

        $comp = [];
        foreach ($result as $item) {
            $comp[] = new DatePeriodDateTime(new DateTime($item[0]), new DateTime($item[1]));
        }

        $this->assertEquals($comp, $date->substract($substract));
    }

    /**
     * @return array
     */
    public function substractProvider() : array
    {
        return [
            'case 1' => [
                ['2016-01-03 00:00:00', '2016-01-10 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                [
                    ['2016-01-03 00:00:00', '2016-01-07 00:00:00'],
                ],
            ],
            'case 2' => [
                ['2016-01-03 00:00:00', '2016-01-07 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                [
                    ['2016-01-03 00:00:00', '2016-01-07 00:00:00'],
                ],
            ],
            'case 3' => [
                ['2016-01-10 00:00:00', '2016-01-15 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                [
                    ['2016-01-14 00:00:00', '2016-01-15 00:00:00'],
                ],
            ],
            'case 4' => [
                ['2016-01-14 00:00:00', '2016-01-15 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                [
                    ['2016-01-14 00:00:00', '2016-01-15 00:00:00'],
                ],
            ],
            'case 5' => [
                ['2016-01-06 00:00:00', '2016-01-15 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                [
                    ['2016-01-06 00:00:00', '2016-01-07 00:00:00'],
                    ['2016-01-14 00:00:00', '2016-01-15 00:00:00'],
                ],
            ],
            'case 6' => [
                ['2016-01-07 00:00:00', '2016-01-15 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                [
                    ['2016-01-14 00:00:00', '2016-01-15 00:00:00'],
                ],
            ],
            'case 7' => [
                ['2016-01-03 00:00:00', '2016-01-14 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                [
                    ['2016-01-03 00:00:00', '2016-01-07 00:00:00'],
                ],
            ],
            'case 8' => [
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                [],
            ],
            'case 9' => [
                ['2016-01-03 00:00:00', '2016-01-04 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                [
                    ['2016-01-03 00:00:00', '2016-01-04 00:00:00'],
                ],
            ],
            'case 10' => [
                ['2016-01-15 00:00:00', '2016-01-16 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                [
                    ['2016-01-15 00:00:00', '2016-01-16 00:00:00'],
                ],
            ],
            'case 11 - good' => [
                ['2016-01-10 00:00:00', '2016-01-11 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                [],
            ],
        ];
    }


    /**
     * Test contains method
     *
     * @param array $date
     * @param array $substract
     * @param array $result
     *
     * @dataProvider commonProvider
     */
    public function testCommon(array $date, array $substract, ?array $result)
    {
        $date = new DatePeriodDateTime(new DateTime($date[0]), new DateTime($date[1]));
        $substract = new DatePeriodDateTime(new DateTime($substract[0]), new DateTime($substract[1]));

        $comp = null;
        if ($result) {
            $comp = new DatePeriodDateTime(new DateTime($result[0]), new DateTime($result[1]));
        }

        $this->assertEquals($comp, $date->common($substract));
    }

    /**
     * @return array
     */
    public function commonProvider() : array
    {
        return [
            'case 1' => [
                ['2016-01-03 00:00:00', '2016-01-10 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-10 00:00:00'],
            ],
            'case 2' => [
                ['2016-01-03 00:00:00', '2016-01-07 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                null,
            ],
            'case 3' => [
                ['2016-01-10 00:00:00', '2016-01-15 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                ['2016-01-10 00:00:00', '2016-01-15 00:00:00'],
            ],
            'case 4' => [
                ['2016-01-14 00:00:00', '2016-01-15 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                null,
            ],
            'case 5' => [
                ['2016-01-06 00:00:00', '2016-01-15 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
            ],
            'case 6' => [
                ['2016-01-07 00:00:00', '2016-01-15 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
            ],
            'case 7' => [
                ['2016-01-03 00:00:00', '2016-01-14 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
            ],
            'case 8' => [
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
            ],
            'case 9' => [
                ['2016-01-03 00:00:00', '2016-01-04 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                null,
            ],
            'case 10' => [
                ['2016-01-15 00:00:00', '2016-01-16 00:00:00'],
                ['2016-01-07 00:00:00', '2016-01-14 00:00:00'],
                null,
            ],
            'case 11 - good' => [
                ['2016-01-10 00:00:00', '2016-01-11 00:00:00'],
                ['2016-01-10 00:00:00', '2016-01-11 00:00:00'],
                ['2016-01-10 00:00:00', '2016-01-11 00:00:00'],
            ],
        ];
    }
}
