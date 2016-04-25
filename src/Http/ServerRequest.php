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
    private $server = [];

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
        $this->payload = file_get_contents('php://input');

        foreach ($_COOKIE as $name => $value) {
            $this->cookies[$name] = new Cookie($name, $value);
        }

        foreach ($_FILES as $name => $value) {
            if ($value["error"] != 4) {
                $this->files[$name] = new File($name, $value);
            }
        }

        $this->handleServerVars();

        if ($this->method == "POST" &&
            (int)$this->getHeader("Content-Length") > $this->getBytes(ini_get("post_max_size"))
        ) {
            throw new \InvalidArgumentException(sprintf(
                "POST Content-Length of %s bytes exceeds the limit of %s bytes",
                $this->getHeader("Content-Length"),
                $this->getBytes(ini_get("post_max_size"))
            ));
        }
    }

    /**
     * @param string $val
     *
     * @return int
     */
    private function getBytes(string $val) : int
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $val *= 1024 * 1024;
                break;
            case 'k':
                $val *= 1024;
                break;
        }

        return $val;
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
