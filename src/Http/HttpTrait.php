<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types = 1);

namespace Cawa\Http;

trait HttpTrait
{
    /**
     * @param string $name
     *
     * @return string
     */
    public function normalizeHeader(string $name) : string
    {
        return str_replace(' ', '-', ucfirst(str_replace('-', ' ', $name)));
    }

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function getHeader(string $name)
    {
        $name = strtolower($name);
        foreach ($this->headers as $current => $value) {
            if (strtolower($current) == $name) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getHeaders() : array
    {
        return $this->headers;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return bool
     */
    public function addHeaderIfNotExist(string $name, string $value) : bool
    {
        $name = $this->normalizeHeader($name);
        if (isset($this->headers[$name])) {
            return false;
        }

        $this->headers[$name] = $value;

        return true;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return bool
     */
    public function addHeader(string $name, string $value) : bool
    {
        $name = $this->normalizeHeader($name);
        if (isset($this->headers[$name])) {
            throw new \InvalidArgumentException(
                sprintf("Header %s is already set with value '%s'", $name, $this->headers[$name])
            );
        }

        $this->headers[$name] = $value;

        return true;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function removeHeader(string $name)
    {
        if (!isset($this->headers[$name])) {
            return false;
        }
        unset($this->headers[$name]);

        return true;
    }

    /**
     * @var Cookie[]
     */
    protected $cookies = [];

    /**
     * @param string $name
     *
     * @return Cookie|null
     */
    public function getCookie(string $name)
    {
        return $this->cookies[$name] ?? null;
    }

    /**
     * @return Cookie[]
     */
    public function getCookies() : array
    {
        return $this->cookies;
    }

    /**
     * @param Cookie $cookie
     *
     * @throws \InvalidArgumentException
     */
    public function addCookie(Cookie $cookie)
    {
        if (isset($this->cookies[$cookie->getName()])) {
            throw new \InvalidArgumentException(sprintf("Cookie '%s' is allready set", $cookie->getName()));
        }

        $this->cookies[$cookie->getName()] = $cookie;
    }

    /**
     * @param Cookie $cookie
     *
     * @throws \InvalidArgumentException
     */
    public function setCookie(Cookie $cookie)
    {
        $this->cookies[$cookie->getName()] = $cookie;
    }

    /**
     * @param Cookie $cookie
     */
    public function clearCookie(Cookie $cookie)
    {
        $cookie->setExpire(1);

        $this->cookies[$cookie->getName()] = $cookie;
    }

    /**
     * @var string
     */
    protected $body;

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body)
    {
        $this->body = $body;
    }
}
