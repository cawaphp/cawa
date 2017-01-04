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

use Cawa\Intl\TranslatorFactory;
use Symfony\Component\Translation\TranslatorInterface;

class Translator implements TranslatorInterface
{
    use TranslatorFactory;

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        if ($locale) {
            throw new \Exception("Can't set locale");
        }

        return self::translator()->trans('carbon.' . $id, $parameters, false);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        if ($locale) {
            throw new \Exception("Can't set locale");
        }

        $trans = self::translator()->transChoice('carbon.' . $id, $number, $parameters, false);
        if (is_null($trans) || $trans == '') {
            return $id;
        } else {
            return $trans;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function setLocale($locale)
    {
        throw new \LogicException("Can't set locale");
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return self::locale();
    }
}
