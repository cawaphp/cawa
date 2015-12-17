<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Cawa\Log\Output;

use Cawa\Log\Event;
use Psr\Log\LogLevel;

abstract class AbstractOutput
{
    const LOGLEVEL = [
        LogLevel::DEBUG,
        LogLevel::INFO,
        LogLevel::NOTICE,
        LogLevel::WARNING,
        LogLevel::ERROR,
        LogLevel::CRITICAL,
        LogLevel::ALERT,
        LogLevel::EMERGENCY
    ];

    /**
     * @var int
     */
    private $minimunLevel;

    /**
     * @param string $minimunLevel
     *
     * @return $this
     */
    public function setMinimumLevel(string $minimunLevel) : self
    {
        $this->minimunLevel = array_search($minimunLevel, self::LOGLEVEL);

        return $this;
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    abstract protected function send(Event $event) : bool;

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function receive(Event $event) : bool
    {
        if ($this->minimunLevel &&
            array_search($event->getLevel(), self::LOGLEVEL) < $this->minimunLevel) {
            return false;
        }

        return $this->send($event);
    }
}
