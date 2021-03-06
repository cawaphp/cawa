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

namespace Cawa\Http;

class Response
{
    use HttpTrait;

    /**
     * @param string $response
     */
    public function __construct(string $response = null)
    {
        if ($response) {
            $explode = explode("\r\n\r\n", $response);

            // multiple query (follow redirect) take only the last request
            if (sizeof($explode) > 2) {
                $start = null;
                foreach ($explode as $index => $current) {
                    if (stripos($current, 'HTTP') === 0) {
                        $start = $index;
                    } else {
                        break;
                    }
                }

                $explode = array_slice($explode, $start);
            }

            $headersString = array_shift($explode);
            $this->body = implode("\r\n\r\n", $explode);

            // headers & cookies
            $headers = [];
            foreach (explode("\n", $headersString) as $i => $header) {
                $explode = explode(':', $header, 2);
                $key = $this->normalizeHeader($explode[0]);
                $value = isset($explode[1]) ? trim($explode[1]) : null;

                if ($key == 'Set-Cookie') {
                    $cookie = Cookie::parse($value);
                    $this->cookies[$cookie->getName()] = $cookie;
                } elseif (array_key_exists($key, $headers)) {
                    $this->headers[$key] .= ', ' . $value;
                } elseif ($value) {
                    $this->headers[$key] = $value;
                } elseif (preg_match('#HTTP/\d+\.\d+ (\d+)#', $header, $matches)) {
                    $this->statusCode = (int) $matches[1];
                }
            }
        }
    }

    /**
     * @var array
     */
    protected static $statusCodeList = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        449 => 'Retry With',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
    ];

    /**
     * @param int $code
     *
     * @return string
     */
    public function getStatusString(int $code) : string
    {
        return $code . ' ' . self::$statusCodeList[$code];
    }

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @return int
     */
    public function getStatus() : int
    {
        return $this->statusCode;
    }
}
