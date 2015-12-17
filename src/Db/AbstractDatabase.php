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

use Cawa\Core\App;
use Cawa\Db\Exceptions\QueryException;
use Cawa\Events\TimerEvent;
use Cawa\Uri\Uri;

abstract class AbstractDatabase
{
    /**
     * @var Uri
     */
    protected $uri;

    /**
     * @return Uri
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string|Uri $connectionString
     *
     * @return AbstractDatabase
     */
    public static function create($connectionString)
    {
        if (!$connectionString instanceof Uri) {
            $uri = new Uri($connectionString);
        } else {
            $uri = $connectionString;
        }

        $scheme = ucfirst($uri->getScheme());

        $class = explode('\\', get_class());
        array_pop($class);
        $class[] = strpos($scheme, 'Pdo') === 0 ? 'Pdo' : $scheme;
        $class[] = $scheme;
        $class = implode('\\', $class);

        $db = new $class();
        $db->uri = $uri;

        return $db;
    }

    /**
     * @var bool
     */
    protected $connected = false;

    /**
     *
     */
    protected function connect()
    {
        $event = new TimerEvent('db.connection');
        $event->addData([
            'hostname' =>  $this->uri->getHost(),
            'user' =>  $this->uri->getUser(),
            'port' =>  $this->uri->getPort(),
        ]);

        $this->openConnection();
        $this->connected = true;

        App::events()->emit($event);
    }

    /**
     *
     */
    abstract protected function openConnection();

    /**
     *
     */
    abstract protected function closeConnection();

    /**
     * @param TimerEvent $event
     * @param AbstractResult $result
     * @param string $sql
     */
    protected function emitQueryEvent(TimerEvent $event, AbstractResult $result = null, string $sql = null)
    {
        $event->addData([
            'hostname' =>  $this->uri->getHost(),
            'database' => substr($this->uri->getPath(), 1),
            'query' => $result ? $result->getQuery() : $sql,
            'affected' => $result ? $result->affectedRows() : null,
            'count' => $result ? ($result->isUnbuffered() ? null : $result->count()) : null,
        ]);

        App::events()->emit($event);
    }

    /**
     * @param string $sql
     * @param array $params
     *
     * @return array
     */
    private function getFinalQuery(string $sql, array $params = []) : string
    {
        if (sizeof($params) == 0 || is_numeric(array_keys($params)[0])) {
            return $sql;
        }

        $split = preg_split('`([\\:\\!][A-Za-z0-9]+)`', $sql, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($split as $index => $item) {
            if (substr($item, 0, 1) == ':') {
                // ! is for unquote values
                $name = substr($item, 1);
                if (!array_key_exists($name, $params)) {
                    throw new QueryException(
                        $this,
                        $sql,
                        sprintf("Missing parameters '%s'", $name)
                    );
                }

                $split[$index] = $this->escape($params[$name]);
            } elseif (substr($item, 0, 1) == '!') {
                // ! is for unquote values
                $name = substr($item, 1);
                if (!array_key_exists($item, $params)) {
                    throw new QueryException(
                        $this,
                        $sql,
                        sprintf("Missing parameters '%s'", $name)
                    );
                }

                $split[$index] = $params[$item];
            }
        }

        return implode('', $split);
    }

    /**
     * @param string $sql
     * @param array $params
     * @param bool $unbuffered
     *
     * @return AbstractResult
     */
    public function query(string $sql, array $params = [], bool $unbuffered = false) : AbstractResult
    {
        if ($this->connected == false) {
            $this->connect();
        }

        $event = new TimerEvent('db.query');

        $finalQuery = $this->getFinalQuery($sql, $params);
        try {
            $result = $this->execute($finalQuery, $unbuffered);
        } catch (QueryException $exception) {
            $this->emitQueryEvent($event, null, $sql);
            throw $exception;
        }

        $this->emitQueryEvent($event, $result);

        return $result;
    }

    /**
     * @param string $sql
     * @param array $params
     *
     * @throws QueryException
     *
     * @return array|bool
     */
    public function fetchOne(string $sql, array $params = [])
    {
        $result = $this->query($sql, $params);

        if ($result->count() > 1) {
            throw new QueryException(
                $this,
                $this->getFinalQuery($sql, $params),
                sprintf('Fetch incomplete resultset, %s remaining', $result->count() - 1)
            );
        }

        if ($result->valid()) {
            return $result->current();
        } else {
            return false;
        }
    }

    /**
     * @param string $sql
     * @param array $params
     *
     * @throws QueryException
     *
     * @return array|bool
     */
    public function fetchAll(string $sql, array $params = []) : array
    {
        $result = $this->query($sql, $params, true);

        $return = [];

        foreach ($result as $item) {
            $return[] = $item;
        }

        return $return;
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    protected function escape($data)
    {
        if ($data instanceof \DateTime) {
            return "'" . $data->format('Y-m-d H:i:s') . "'";
        } elseif (is_null($data)) {
            return 'NULL';
        } elseif (is_numeric($data) && substr((string) $data, 0, 1) != "+"  && substr((string) $data, 0, 1) != "-") {
            // @see http://php.net/manual/en/function.is-numeric.php
            // Thus +0123.45e6 is a valid numeric value : we don't want
            return (string) $data;
        } elseif (is_bool($data)) {
            return $data === true ? 'TRUE' : 'FALSE';
        } else {
            return null;
        }
    }

    /**
     * @param string $sql
     * @param bool $unbuffered
     *
     * @return \Cawa\Db\AbstractResult
     */
    abstract protected function execute(string $sql, bool $unbuffered);
}
