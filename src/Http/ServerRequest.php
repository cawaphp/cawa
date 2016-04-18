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

namespace Cawa\Http;

use Cawa\Net\Uri;

class ServerRequest extends Request
{
    /**
     * @var array
     */
    private $server;

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function getServer(string $name)
    {
        $name = strtoupper($name);
        foreach ($this->server as $current => $value) {
            if ($current == $name) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getServers() : array
    {
        return $this->server;
    }

    /**
     *
     */
    public function fillFromGlobals()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? null;
        $this->uri = new Uri();
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->payload = file_get_contents('php://input');

        foreach ($_COOKIE as $name => $value) {
            $this->cookies[$name] = new Cookie($name, $value);
        }

        $this->handleServerVars();
    }

    /**
     * @return void
     */
    private function handleServerVars()
    {
        // With the php's bug #66606, the php's built-in web server
        // stores the Content-Type and Content-Length header values in
        // HTTP_CONTENT_TYPE and HTTP_CONTENT_LENGTH fields.
        $server = $_SERVER;
        if ('cli-server' === php_sapi_name()) {
            if (array_key_exists('HTTP_CONTENT_LENGTH', $_SERVER)) {
                $server['CONTENT_LENGTH'] = $_SERVER['HTTP_CONTENT_LENGTH'];
            }
            if (array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) {
                $server['CONTENT_TYPE'] = $_SERVER['HTTP_CONTENT_TYPE'];
            }
        }

        foreach ($server as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $this->headers[$key] = $value;
            } else {
                $this->server[strtoupper($name)] = $value;
            }
        }
    }
}
