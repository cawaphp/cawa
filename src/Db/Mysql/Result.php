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

namespace Cawa\Db\Mysql;

use Cawa\Db\AbstractResult;

class Result extends AbstractResult
{
    /**
     * @var \mysqli_result
     */
    private $result;

    /**
     * Result constructor.
     *
     * @param string $query
     * @param \mysqli_result|bool $result
     * @param int $insertedId
     * @param int $affectedRows
     * @param bool $isUnbuffered
     */
    public function __construct(
        string $query,
        $result,
        bool $isUnbuffered,
        int $insertedId = null,
        int $affectedRows = null
    ) {
        parent::__construct($query, $isUnbuffered);

        if (!$result instanceof \mysqli_result && !is_bool($result)) {
            throw new \RuntimeException(sprintf("Invalid result type '%s'", gettype($result)));
        }

        $this->result = $result;
        $this->insertedId = $insertedId;
        $this->affectedRows = $affectedRows;
    }

    /**
     * {@inheritdoc}
     */
    protected function load() : bool
    {
        $this->currentData = null;

        if (($data = $this->result->fetch_assoc()) === null) {
            return false;
        }

        $this->position++;
        $this->currentData = $data;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        if ($this->position !== 0 && $this->isUnbuffered === true) {
            throw new \RuntimeException('Unbuffered results cannot be rewound for multiple iterations');
        }

        if ($this->isUnbuffered === false) {
            $this->result->data_seek(0);
        }

        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (is_bool($this->result)) {
            return 0;
        }

        if ($this->isUnbuffered === true) {
            throw new \RuntimeException('Row count is not available in unbuffered result sets.');
        }

        return $this->result->num_rows;
    }

    /**
     * @var int
     */
    private $insertedId;

    /**
     * {@inheritdoc}
     */
    public function insertedId() : int
    {
        return $this->insertedId;
    }

    /**
     * @var int
     */
    private $affectedRows;

    /**
     * {@inheritdoc}
     */
    public function affectedRows() : int
    {
        return $this->affectedRows;
    }
}
