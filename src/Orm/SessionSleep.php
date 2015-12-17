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

namespace Cawa\Orm;

trait SessionSleep
{
    /**
     * @return array
     */
    abstract protected function sessionSleep() : array;

    /**
     * @param array $data
     *
     * @return static
     */
    abstract protected static function sessionWakeup(array $data);
}
