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

    const FORMAT_SHORT = 1;

    /**
     * @param float $value
     *
     * @return string
     */
    public static function format(float $value) : string
    {
        $formatter = new NumberFormatter(self::translator()->getIETF(), NumberFormatter::DECIMAL);

        return $formatter->format($value);
    }

    /**
     * @param float $value
     * @param string $currency
     * @param int|null $format
     *
     * @return string
     */
    public static function formatCurrency(float $value, string $currency, int $format = null) : string
    {
        $formatter = new NumberFormatter(self::translator()->getIETF(), NumberFormatter::CURRENCY);

        if ($format && $format & self::FORMAT_SHORT) {
            $diff = pow(10, -$formatter->getAttribute(NumberFormatter::FRACTION_DIGITS));
            $decimal = (float) (floatval((string) $value) - (int) floatval((string) $value));

            if ($decimal < $diff) {
                $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
            }
        }

        return $formatter->formatCurrency($value, $currency);
    }

    /**
     * @param float $value
     *
     * @return string
     */
    public static function formatPercent(float $value) : string
    {
        $formatter = new NumberFormatter(self::translator()->getIETF(), NumberFormatter::PERCENT);

        return $formatter->format($value);
    }
}
