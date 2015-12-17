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
use Cawa\Http\Response;

class RequestException extends ConnectionException
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @param Request $request
     * @param Response $response
     * @param int $code
     * @param \Throwable $previous
     */
    public function __construct(Request $request, Response $response, int $code, \Throwable $previous = null)
    {
        $this->response = $response;
        $message = $response->getBody() ? $response->getBody() : 'Empty response body';
        if (strlen($message) > 128) {
            $message = substr($message, 0, 128) . ' ...';
        }

        parent::__construct($request, $message, $code, $previous);
    }
}
