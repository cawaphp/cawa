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

namespace Cawa\Intl;

use Cawa\Core\DI;

trait TranslatorFactory
{
    /**
     * @return Translator
     */
    protected static function translator() : Translator
    {
        if ($return = DI::get(__METHOD__)) {
            return $return;
        }

        $item = new Translator();

        return DI::set(__METHOD__, null, $item);
    }

    /**
     * @return string
     */
    protected static function locale() : string
    {
        return self::translator()->getLocale();
    }

    /**
     * @return array
     */
    protected static function locales() : array
    {
        return self::translator()->getLocales();
    }

    /**
     * @param string $key
     * @param array $data
     * @param bool $warmIfMissing
     *
     * @return string|null
     */
    protected static function trans(string $key, array $data = null, bool $warmIfMissing = true)
    {
        return self::translator()->trans($key, $data, $warmIfMissing);
    }

    /**
     * @param string $key
     * @param int $number
     * @param array|null $data
     * @param bool $warmIfMissing
     *
     * @return string
     */
    protected static function transChoice(string $key, int $number, array $data = null, bool $warmIfMissing = true)
    {
        return self::translator()->transChoice($key, $number, $data, $warmIfMissing);
    }

    /**
     * @param string $key
     *
     * @return array
     */
    protected static function transArray(string $key) : array
    {
        return self::translator()->transArray($key);
    }
}
