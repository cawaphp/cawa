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

namespace Cawa\Error\Listeners;

use Cawa\Error\ErrorEvent;

abstract class AbstractListener
{
    /**
     * @param ErrorEvent $event
     */
    abstract public static function receive(ErrorEvent $event);
}
