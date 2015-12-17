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

namespace Cawa\Db\Pdo;

use Cawa\Db\Exceptions\ConnectionException;
use Cawa\Db\Exceptions\QueryException;
use Cawa\Db\TransactionDatabase;

class Pdo extends TransactionDatabase
{
    /**
     * @var \PDO
     */
    private $driver;

    /**
     * {@inheritdoc}
     */
    protected function openConnection() : bool
    {
        if ($this->connected) {
            return true;
        }

        $scheme = strtolower(substr($this->uri->getScheme(), 3));
        $dsn = $scheme . ':';
        $dsn .= 'host=' . $this->uri->getHost() . ';';
        $dsn .= 'dbname=' . substr($this->uri->getPath(), 1);
        if ($this->uri->getPort()) {
            $dsn .= ';port=' . $this->uri->getPort();
        }

        $defaultOptions = [
             \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
             \PDO::ATTR_TIMEOUT => 5,
         ];

        switch ($scheme) {
            case 'mysql':
                $defaultOptions[\PDO::MYSQL_ATTR_COMPRESS] = true;
                $dsn .= ';charset=utf8';
                break;
        }

        $options = $defaultOptions + $this->uri->getQueries();
        try {
            $this->driver = new \PDO($dsn, $this->uri->getUser(), $this->uri->getPassword(), $options);
        } catch (\PDOException $exception) {
            throw new ConnectionException(
                $this,
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        $result = $this->execute('SET NAMES utf8');

        $this->driver->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function closeConnection() : bool
    {
        return $this->driver = null;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(string $sql, bool $unbuffered = false) : Result
    {
        $this->driver->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, !$unbuffered);

        try {
            $result = $this->driver->query($sql);
        } catch (\PDOException $exception) {
            throw new QueryException($this, $sql, $exception->getMessage(), $exception->getCode(), $exception);
        }

        $insertedId = $this->driver->lastInsertId();
        if ($insertedId === '0') {
            $insertedId = null;
        } else {
            $insertedId = (int) $insertedId;
        }

        return new Result($sql, $result, $unbuffered, $insertedId);
    }

    /**
     * {@inheritdoc}
     */
    public function escape($data) : string
    {
        $parentData = parent::escape($data);
        if (is_string($parentData)) {
            return $parentData;
        }

        if (!$this->connected) {
            $this->connect();
        }

        return $this->driver->quote($data) ;
    }
}
