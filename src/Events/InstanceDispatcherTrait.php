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

namespace Cawa\Events;

use Cawa\Core\DI;

trait InstanceDispatcherTrait
{
    /**
     * @return Dispatcher
     */
    public function instanceDispatcher() : Dispatcher
    {
        if ($return = DI::get(get_class($this), spl_object_hash($this))) {
            return $return;
        }

        $item = new Dispatcher();

        return DI::set(get_class($this), spl_object_hash($this), $item);
    }
}
