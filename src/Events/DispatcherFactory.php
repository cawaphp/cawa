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

namespace Cawa\Events;

use Cawa\Core\DI;

trait DispatcherFactory
{
    /**
     * @return Dispatcher
     */
    private static function dispatcher() : Dispatcher
    {
        if ($return = DI::get(__METHOD__)) {
            return $return;
        }

        $item = new Dispatcher();

        return DI::set(__METHOD__, null, $item);
    }
}
