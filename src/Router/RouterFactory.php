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

namespace Cawa\Router;

use Cawa\Core\DI;

trait RouterFactory
{
    /**
     * @var array
     */
    private static $container = [];

    /**
     * @return Router
     */
    protected static function router() : Router
    {
        if ($return = DI::get(__METHOD__)) {
            return $return;
        }

        $item = new Router();

        return DI::set(__METHOD__, null, $item);
    }
}
