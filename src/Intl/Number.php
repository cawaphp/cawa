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
     * @param string $currency
     *
     * @return string
     */
    public static function formatCurrency(float $value, string $currency, int $format = null) : string
    {
        $formatter = new NumberFormatter(self::translator()->getIETF(), NumberFormatter::CURRENCY);

        if ($format && $format & self::FORMAT_SHORT) {
            $diff = pow(10, -$formatter->getAttribute(NumberFormatter::FRACTION_DIGITS));
            $decimal = fmod($value, 1);

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
    public static function formatPourcent(float $value, int $format = null) : string
    {
        $formatter = new NumberFormatter(self::translator()->getIETF(), NumberFormatter::PERCENT);

        if ($format && $format & self::FORMAT_SHORT) {
            $diff = pow(10, -$formatter->getAttribute(NumberFormatter::FRACTION_DIGITS));
            $decimal = fmod($value, 1);

            if ($decimal < $diff) {
                $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
            }
        }

        return $formatter->format($value);
    }
}
