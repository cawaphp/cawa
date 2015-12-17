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

namespace Cawa\Db;

abstract class AbstractResult implements \Iterator, \Countable
{
    /**
     * @param string $query
     * @param bool $isUnbuffered
     */
    public function __construct(string $query, bool $isUnbuffered)
    {
        $this->query = $query;
        $this->isUnbuffered = $isUnbuffered;
    }

    /**
     * @var string
     */
    protected $query;

    /**
     * @return string
     */
    public function getQuery() : string
    {
        return $this->query;
    }

    /**
     * @var bool
     */
    protected $isUnbuffered = false;

    /**
     * @return bool
     */
    public function isUnbuffered() : bool
    {
        return $this->isUnbuffered;
    }

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var array
     */
    protected $currentData = [];

    /**
     * {@inheritdoc}
     */
    public function key() : int
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function current() : array
    {
        return $this->currentData;
    }

    /**
     * {@inheritdoc}
     */
    public function valid() : bool
    {
        return $this->load();
    }

    /**
     * @return bool
     */
    abstract protected function load() : bool;

    /**
     * @return int
     */
    abstract public function insertedId() : int;

    /**
     * @return int
     */
    abstract public function affectedRows() : int;
}
