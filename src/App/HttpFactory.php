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

namespace Cawa\App;

use Cawa\Core\DI;
use Cawa\Http\ServerRequest;
use Cawa\Http\ServerResponse;

trait HttpFactory
{
    /**
     * @return ServerRequest
     */
    protected static function request() : ServerRequest
    {
        if ($return = DI::get(__METHOD__)) {
            return $return;
        }

        $item = new ServerRequest();

        return DI::set(__METHOD__, null, $item);
    }

    /**
     * @return ServerResponse
     */
    protected static function response() : ServerResponse
    {
        if ($return = DI::get(__METHOD__)) {
            return $return;
        }

        $item = new ServerResponse();

        return DI::set(__METHOD__, null, $item);
    }
}
