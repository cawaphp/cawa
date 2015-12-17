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

use Cawa\Core\App;
use Cawa\Db\Exceptions\ConnectionException;
use Cawa\Db\Exceptions\QueryException;
use Cawa\Db\TransactionDatabase;

class Mysql extends TransactionDatabase
{
    /**
     * @var \Mysqli
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

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        // mysqli_report(MYSQLI_REPORT_ALL);

        $defaultOptions = [
            MYSQLI_OPT_CONNECT_TIMEOUT => 5,
            MYSQLI_CLIENT_COMPRESS => true,
        ];

        $this->driver = mysqli_init();

        $flags = null;
        $options = $defaultOptions + $this->uri->getQueries();

        foreach ($options as $key => $value) {
            if ($value == true &&
                in_array($key, [
                    MYSQLI_CLIENT_COMPRESS,
                    MYSQLI_CLIENT_FOUND_ROWS,
                    MYSQLI_CLIENT_IGNORE_SPACE,
                    MYSQLI_CLIENT_INTERACTIVE,
                    MYSQLI_CLIENT_SSL
                ])
            ) {
                $flags = $flags | $key;
            } else {
                if (!$this->driver->options($key, $value)) {
                    throw new ConnectionException(
                        $this,
                        sprintf("Unable to set options '%s' with value '%s'", $key, $value)
                    );
                }
            }
        }

        $connected = @$this->driver->real_connect(
            $this->uri->getHost(),
            $this->uri->getUser(),
            $this->uri->getPassword(),
            substr($this->uri->getPath(), 1),
            $this->uri->getPort() ?? ini_get('mysqli.default_port'),
            '',
            $flags
        );

        if ($this->driver->connect_errno || $connected === false) {
            throw new ConnectionException(
                $this,
                $this->driver->connect_error,
                $this->driver->connect_errno
            );
        }

        $this->execute('SET NAMES utf8');

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function closeConnection() : bool
    {
        return $this->driver->close();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(string $sql, bool $unbuffered = false) : Result
    {
        try {
            if ($unbuffered) {
                $return = $this->driver->query($sql, MYSQLI_USE_RESULT);
            } else {
                $return = $this->driver->query($sql);
            }
        } catch (\mysqli_sql_exception $exception) {

            // no code example : No index used in query/prepared statement
            if (!$exception->getCode()) {
                App::logger()->warning($exception->getMessage());
            } else {
                throw new QueryException($this, $sql, $exception->getMessage(), $exception->getCode(), $exception);
            }
        }

        // never happen due to mysqli_report(MYSQLI_REPORT_ALL);
        if ($return == false) {
            throw new QueryException($this, $sql, $this->driver->error, $this->driver->errno);
        }

        if ($warnings = $this->driver->get_warnings()) {
            $messages = [];
            $code = null;
            do {
                if ($warnings !== true) {
                    $code = $warnings->errno;
                    $messages[] = '#' . $code . ' ' . $warnings->message;
                }
            } while ($warnings->next());

            throw new QueryException($this, $sql, implode(' | ', $messages), $code);
        }

        return new Result($sql, $return, $unbuffered, $this->driver->insert_id, $this->driver->affected_rows);
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

        return "'" . $this->driver->escape_string($data) . "'";
    }
}
