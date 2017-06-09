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

namespace Cawa\Intl;

use NumberFormatter;

class Number
{
    use TranslatorFactory;

    /**
     * @param float $value
     * @param string $currency
     *
     * @return string
     */
    public static function formatCurrency(float $value, string $currency) : string
    {
        $formatter = new NumberFormatter(self::translator()->getIETF(), NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($value, $currency);
    }
}
