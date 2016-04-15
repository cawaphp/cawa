<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Cawa\Intl;


trait TraitIntl
{
    use TranslatorFactory;

    /**
     * @param string $name
     * @param array $data
     *
     * @return string
     */
    public function trans(string $name, array $data = null)
    {
        return self::translator()->trans($name, $data);
    }

    /**
     * @param string $name
     * @param int $number
     * @param array $data
     *
     * @return string
     */
    public function transChoice(string $name, int $number, array $data = null)
    {
        return self::translator()->transChoice($name, $number, $data);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function transArray(string $name)
    {
        return self::translator()->transArray($name);
    }

}
