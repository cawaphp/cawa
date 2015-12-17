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

namespace Cawa\Http\Exceptions;

use Cawa\Http\Request;

abstract class AbstractException extends \ErrorException
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * ConnectionException constructor.
     *
     * @param Request $request
     * @param int $message
     * @param int $code
     * @param \Throwable $previous
     */
    public function __construct(
        Request $request,
        string $message,
        int $code = null,
        \Throwable $previous = null
    ) {
        $this->request = $request;

        $message = sprintf(
            '[%s %s] [ERROR %s] ',
            $request->getMethod(),
            $request->getUri()->get(false, false),
            $code
        ) . htmlspecialchars($message);

        $filename = __FILE__;
        $lineno = __LINE__;

        $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        foreach ($debug as $index => $backtrace) {
            if (isset($backtrace['class']) &&
                $backtrace['class'] == 'Cawa\\Http\\Client' &&
                (
                    empty($debug[$index + 1]['class']) ||
                    (
                        !empty($debug[$index + 1]['class']) &&
                        $debug[$index + 1]['class'] != 'Cawa\\Http\\Client'
                    )
                )

            ) {
                $filename = $debug[$index]['file'];
                $lineno = $debug[$index]['line'];
                break;
            }
        }

        parent::__construct($message, $code, 1, $filename, $lineno, $previous);
    }
}
