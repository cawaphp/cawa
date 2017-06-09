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

namespace Cawa\Router;

use Cawa\Core\DI;
use Cawa\Net\Uri;

trait RouterFactory
{
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

    /**
     * @param string $name
     * @param array $data
     * @param bool $warnData
     *
     * @return Uri
     */
    protected static function uri(string $name, array $data = [], $warnData = true) : Uri
    {
        return self::router()->getUri($name, $data, $warnData);
    }
}
