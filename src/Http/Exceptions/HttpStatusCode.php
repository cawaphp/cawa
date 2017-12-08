<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Cawa\Http\Exceptions;

use Error;
use Throwable;

class HttpStatusCode extends Error
{
    /**
     * @var string|array|\SimpleXMLElement
     */
    private $response;

    /**
     * @return array|\SimpleXMLElement|string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param array|\SimpleXMLElement|string $response
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($response, int $code = 0, Throwable $previous = null)
    {
        $this->response = $response;

        parent::__construct('', $code, $previous);
    }
}
