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

class Event
{
    /**
     * @param string $name
     * @param array $data
     *
     * @return $this|self
     */
    public static function instance(string $name, array $data = []) : self
    {
        return new static($name, $data);
    }

    /**
     * @param string $name
     * @param array $data
     */
    public function __construct(string $name, array $data = [])
    {
        if (strstr($name, '.', true) === false) {
            throw new \InvalidArgumentException(sprintf("Invalid event name '%s'", $name));
        }

        $this->name = $name;
        $this->data = $data;
    }

    /**
     * The name of the events.
     *
     * @var string
     */
    private $name;

    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNamespace() : string
    {
        return strstr($this->name, '.', true);
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return substr(strstr($this->name, '.', false), 1);
    }

    /**
     * The data of the events.
     *
     * @var array
     */
    private $data = [];

    /**
     * Gets the data.
     *
     * @return string
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Sets the data.
     *
     * @param array $data
     *
     * @return $this|self
     */
    public function setData(array $data) : self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Add some data.
     *
     * @param array $data
     *
     * @return $this|self
     */
    public function addData(array $data) : self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Action perform during emit.
     */
    public function onEmit()
    {
    }
}
