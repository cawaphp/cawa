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

use Cawa\Db\Exceptions\QueryException;
use Cawa\Events\TimerEvent;

abstract class TransactionDatabase extends AbstractDatabase
{
    /**
     * @var bool
     */
    protected $transactionStarted = false;

    /**
     * @return bool
     */
    public function isTransactionStarted() : bool
    {
        return $this->transactionStarted;
    }

    /**
     * Start a sql transaction
     *
     * @throws QueryException
     *
     * @return bool
     */
    public function startTransaction() : bool
    {
        $sql = 'START TRANSACTION';

        if ($this->transactionStarted) {
            $event = new TimerEvent('db.query');
            $this->emitQueryEvent($event, null, $sql);

            throw new QueryException(
                $this,
                $sql,
                "Can't start transaction, already started"
            );
        }

        $this->query($sql);
        $this->transactionStarted = true;

        return true;
    }

    /**
     * Rollback a sql transaction
     *
     * @throws QueryException
     *
     * @return bool
     */
    public function rollback() : bool
    {
        $sql = 'ROLLBACK';

        if (!$this->transactionStarted) {
            $event = new TimerEvent('db.query');
            $this->emitQueryEvent($event, null, $sql);

            throw new QueryException(
                $this,
                $sql,
                "Can't rollback unstarted transaction"
            );
        }

        $this->query($sql);
        $this->transactionStarted = false;

        return true;
    }

    /**
     * Commit a sql transaction
     *
     * @throws QueryException
     *
     * @return bool
     */
    public function commit() : bool
    {
        $sql = 'COMMIT';

        if (!$this->transactionStarted) {
            $event = new TimerEvent('db.query');
            $this->emitQueryEvent($event, null, $sql);

            throw new QueryException(
                $this,
                $sql,
                "Can't commit unstarted transaction"
            );
        }

        $this->query($sql);
        $this->transactionStarted = false;

        return true;
    }
}
