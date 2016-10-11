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

namespace Cawa\Log;

use Cawa\Events\DispatcherFactory;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class Logger extends AbstractLogger implements LoggerInterface
{
    use DispatcherFactory;

    /**
     * Logs with an arbitrary level.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     *
     * @return null
     */
    public function log($level, $message, array $context = [])
    {
        if (isset($context['name'])) {
            $name = $context['name'];
            unset($context['name']);
        } else {
            $name = 'log.' . $level;
        }

        $event = new Event($name);
        $event->setLevel($level)
            ->setMessage($message)
            ->setContext($context);

        self::emit($event);
    }
}
