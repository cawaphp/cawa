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

abstract class AbstractException extends \ErrorException
{
    /**
     * @param AbstractDatabase $db
     * @param string $message
     * @param int $code
     * @param \Throwable $previous
     */
    public function __construct(
        AbstractDatabase $db,
        string $message,
        int $code = null,
        \Throwable $previous = null
    ) {
        $message = sprintf(
            '[%s.%s] [Error: %s] ',
            $db->getUri()->getHost(),
            substr($db->getUri()->getPath(), 1),
            $code
        ) . $message;

        $filename = __FILE__;
        $lineno = __LINE__;

        $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        foreach ($debug as $index => $backtrace) {
            if (isset($backtrace['class']) &&
                $backtrace['class'] == 'Cawa\\Db\\AbstractDatabase' &&
                (
                    !isset($debug[$index + 1]['class']) ||
                    stripos('Cawa\\Db', $debug[$index + 1]['class']) === false
                )
            ) {
                $filename = $debug[$index]['file'];
                $lineno = $debug[$index]['line'];
                break;
            }
        }

        parent::__construct($message, (int) $code, 1, $filename, $lineno, $previous);
    }
}
