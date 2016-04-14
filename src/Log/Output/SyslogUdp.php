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

class SyslogUdp extends Syslog
{
    /**
     * @var string
     */
    protected $ip;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var int
     */
    protected $pid;

    /**
     * @var resource
     */
    protected $resource;

    /**
     * Udp constructor.
     *
     * @param int $facility
     * @param string $name
     * @param string $ip
     * @param int $port
     * @param bool $hostname
     * @param bool $pid
     */
    public function __construct(
        int $facility,
        string $name,
        string $ip = 'localhost',
        int $port = 514,
        bool $hostname = false,
        bool $pid = false
    ) {
        parent::__construct($facility, $name);

        $this->ip = $ip;
        $this->port = $port;
        $this->hostname = $hostname ? gethostname() : null;
        $this->pid = $pid ? getmypid() : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function send(Event $event) : bool
    {
        if (!is_resource($this->resource)) {
            $this->resource = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        }

        $message = $event->format(false);

        $udpMessage = '<' . ($this->facility + $this->convertType($event->getLevel())) . '>' .
            $event->getDate()->format('M d H:i:s') . ' ' .
            ($this->hostname ? $this->hostname . ' ': '') .
            $this->name .
            ($this->pid ? '[' . $this->pid . ']' : '') .
            ': ' . $message . "\0";

        if (strlen($udpMessage) > self::DATAGRAM_MAX_LENGTH) {
            $udpMessage = substr($udpMessage, 0, self::DATAGRAM_MAX_LENGTH);
        }

        $length = strlen($udpMessage);
        $size = socket_sendto($this->resource, $udpMessage, $length, $flags = 0, $this->ip, $this->port);

        return $size == $length;
    }
}
