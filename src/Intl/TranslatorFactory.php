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
}
