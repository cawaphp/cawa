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

namespace Cawa\Log\Output;

use Cawa\Log\Event;

class StdErr extends AbstractOutput
{
    /**
     * {@inheritdoc}
     */
    protected function send(Event $event) : bool
    {
        file_put_contents('php://stderr', $event->format());

        return true;
    }
}
