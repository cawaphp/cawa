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

namespace Cawa\Db\Exceptions;

use Cawa\Db\AbstractDatabase;

class QueryException extends AbstractException
{
    /**
     * ConnectionException constructor.
     *
     * @param AbstractDatabase $db
     * @param string $query
     * @param int $message
     * @param int $code
     * @param \Throwable $previous
     */
    public function __construct(
        AbstractDatabase $db,
        string $query,
        string $message,
        int $code = 0,
        \Throwable $previous = null
    ) {
        $message = sprintf('[Query: %s] ', $query) . $message;

        parent::__construct($db, $message, $code, $previous);
    }
}
