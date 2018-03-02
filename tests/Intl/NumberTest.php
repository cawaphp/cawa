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

namespace CawaTest\Intl;

use Cawa\Core\DI;
use Cawa\Intl\Number;
use PHPUnit\Framework\TestCase;

class NumberTest extends TestCase
{
    /**
     *
     */
    public function setUp()
    {
        DI::config()->load(__DIR__ . '/../config.yml');
    }

    /**
     * @param float $amount
     * @param string $currency
     * @param int $format
     * @param string $return
     *
     * @dataProvider formatCurrencyProvider
     */
    public function testFormatCurrency(float $amount, string $currency, int $format = null, string $return)
    {
        $this->assertEquals($return, Number::formatCurrency($amount, $currency, $format));
    }

    /**
     * @return array
     */
    public function formatCurrencyProvider()
    {
        return [
            [1, 'EUR', null, '1,00 €'],
            [1001.23, 'EUR', null, '1 001,23 €'],
            [1, 'EUR', Number::FORMAT_SHORT, '1 €'],
            [1001.23, 'EUR', Number::FORMAT_SHORT, '1 001,23 €'],
            [1001.00, 'EUR', Number::FORMAT_SHORT, '1 001 €'],
            [1001.001, 'EUR', Number::FORMAT_SHORT, '1 001 €'],
            [25875.0, 'EUR', Number::FORMAT_SHORT, '25 875 €'],
            [15000/2, 'EUR', Number::FORMAT_SHORT, '7 500 €'],
            [100/3, 'EUR', Number::FORMAT_SHORT, '33,33 €'],
            [45000*1.15/2, 'EUR', Number::FORMAT_SHORT, '25 875 €'],
        ];
    }
}
