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

namespace Cawa\Http\Client;

use Cawa\Http\Client;
use Cawa\Http\Request;
use Cawa\Http\Response;

abstract class AbstractClient
{
    const OPTIONS_SSL_VERIFY = 'SSL_VERIFY';
    const OPTIONS_SSL_CLIENT_CERTIFICATE = 'SSL_CLIENT_CERTIFICATE';
    const OPTIONS_SSL_CLIENT_KEY = 'SSL_CLIENT_KEY';
    const OPTIONS_TIMEOUT = 'TIMEOUT';
    const OPTIONS_CONNECT_TIMEOUT = 'CONNECT_TIMEOUT';
    const OPTIONS_PROXY = 'PROXY';
    const OPTIONS_DEBUG = 'DEBUG';

    /**
     * @var array
     */
    protected $options = [
        self::OPTIONS_CONNECT_TIMEOUT => 5000,
        self::OPTIONS_TIMEOUT => 5000,
        self::OPTIONS_DEBUG => true,
    ];

    /**
     * @var array
     */
    protected $defaultHeader = [
        'UserAgent' => 'Cawa PHP Client',
    ];

    /**
     * @param string $name
     * @param $value
     *
     * @return $this
     */
    public function setDefaultHeader(string $name, $value)
    {
        $this->defaultHeader[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function setOption(string $name, $value) : self
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    abstract public function request(Request $request) : Response;
}
