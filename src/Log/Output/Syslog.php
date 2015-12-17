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
use Psr\Log\LogLevel;

class Syslog extends AbstractOutput
{
    /**
     * Max length of udp packet
     */
    const DATAGRAM_MAX_LENGTH = 65023;

    /**
     * kernel messages
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_KERN = LOG_KERN;

    /**
     * generic user-level messages
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_USER = LOG_USER;

    /**
     * mail subsystem
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_MAIL = LOG_MAIL;

    /**
     * other system daemons
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_DAEMON = LOG_DAEMON;

    /**
     * security/authorization messages (use <b>LOG_AUTHPRIV</b> instead
     * in systems where that constant is defined)
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_AUTH = LOG_AUTH;

    /**
     * messages generated internally by syslogd
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_SYSLOG = LOG_SYSLOG;

    /**
     * line printer subsystem
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_LPR = LOG_LPR;

    /**
     * USENET news subsystem
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_NEWS = LOG_NEWS;

    /**
     * UUCP subsystem
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_UUCP = LOG_UUCP;

    /**
     * clock daemon (cron and at)
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_CRON = LOG_CRON;

    /**
     * security/authorization messages (private)
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_AUTHPRIV = LOG_AUTHPRIV;

    /**
     * generic local messages
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_LOCAL0 = LOG_LOCAL0;

    /**
     * generic local messages
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_LOCAL1 = LOG_LOCAL1;

    /**
     * generic local messages
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_LOCAL2 = LOG_LOCAL2;

    /**
     * generic local messages
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_LOCAL3 = LOG_LOCAL3;

    /**
     * generic local messages
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_LOCAL4 = LOG_LOCAL4;

    /**
     * generic local messages
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_LOCAL5 = LOG_LOCAL5;

    /**
     * generic local messages
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_LOCAL6 = LOG_LOCAL6;

    /**
     * generic local messages
     *
     * @link http://php.net/manual/en/network.constants.php
     */
    const FACILITY_LOG_LOCAL7 = LOG_LOCAL7;

    /**
     * @var string
     */
    protected $facility;

    /**
     * @var string
     */
    protected $name;

    /**
     * Udp constructor.
     *
     * @param int $facility
     * @param string $name
     */
    public function __construct(int $facility, string $name)
    {
        $this->facility = $facility;
        $this->name = $name;
    }

    /**
     * @param string $type
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    protected function convertType(string $type) : int
    {
        switch ($type) {
            case LogLevel::EMERGENCY:
                return LOG_EMERG;
            case LogLevel::ALERT:
                return LOG_ALERT;
            case LogLevel::CRITICAL:
                return LOG_CRIT;
            case LogLevel::ERROR:
                return LOG_ERR;
            case LogLevel::WARNING:
                return LOG_WARNING;
            case LogLevel::NOTICE:
                return LOG_NOTICE;
            case LogLevel::INFO:
                return LOG_INFO;
            case LogLevel::DEBUG:
                return LOG_DEBUG;
            default:
                throw new \InvalidArgumentException(sprintf("Invalid log type '%s'", $type));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function send(Event $event) : bool
    {
        $message = $event->format(false);

        openlog($this->name, LOG_ODELAY, $this->facility);
        $return = syslog($this->convertType($event->getLevel()), $message);
        closelog();

        return $return;
    }
}
