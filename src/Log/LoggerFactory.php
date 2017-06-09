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

namespace Cawa\Log;

use Cawa\Core\DI;

trait LoggerFactory
{
    /**
     * @return Logger
     */
    private static function logger() : Logger
    {
        if ($return = DI::get(__METHOD__)) {
            return $return;
        }

        $item = new Logger();

        return DI::set(__METHOD__, null, $item);
    }
}
