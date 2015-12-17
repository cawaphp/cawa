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

namespace Cawa\Log;

use Cawa\Date\DateTime;
use Cawa\Events\Event as EventBase;

class Event extends EventBase
{
    /**
     * The Level
     *
     * @var string
     */
    private $level;

    /**
     * Gets Level
     *
     * @return string
     */
    public function getLevel() : string
    {
        return $this->level;
    }

    /**
     * Set Level
     *
     * @param string $Level
     *
     * @return Event
     */
    public function setLevel(string $Level) : self
    {
        $this->level = $Level;

        return $this;
    }

    /**
     * The message
     *
     * @var string
     */
    private $message;

    /**
     * Gets message
     *
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Event
     */
    public function setMessage(string $message) : self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * The message
     *
     * @var array
     */
    private $context;

    /**
     * Gets message
     *
     * @return string
     */
    public function getContext() : array
    {
        return $this->context;
    }

    /**
     * Set message
     *
     * @param array $context
     *
     * @return Event
     */
    public function setContext(array $context) : self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * The message
     *
     * @var \DateTime
     */
    private $date;

    /**
     * Gets message
     *
     * @return DateTime
     */
    public function getDate() : DateTime
    {
        return $this->date;
    }

    /**
     * @param bool $date
     * @param bool $context
     *
     * @return string
     */
    public function format(bool $date = true, bool $context = true) : string
    {
        $return = $context ? $this->context : [];

        $return = ['Message' => $this->message] + $return ;
        $return = $date ? (['Date' => $this->date->format('Y-m-d H:i:s')] + $return) : $return;

        return '[' .
            implode(
                '] [',
                array_map(
                    function (
                        $v,
                        $k
                    ) {
                        return sprintf(
                            '%s: %s',
                            ucfirst(strtolower($k)),
                            $v
                        );
                    },
                    $return,
                    array_keys($return)
                )
            ) .
            ']';
    }

    /**
     * Add current date
     */
    public function onEmit()
    {
        $microtime = explode('.', (string) microtime(true));

        $this->date = DateTime::parse(date('Y-m-d\TH:i:s') . '.' .
            ($microtime[1] ?? '0000'));
    }
}
