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
     * @return $this|HttpTrait|static
     */
    public function addHeaderIfNotExist(string $name, string $value) : self
    {
        $name = $this->normalizeHeader($name);
        if (isset($this->headers[$name])) {
            return $this;
        }

        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * @param array $headers
     *
     * @return $this|HttpTrait|static
     */
    public function addHeaders(array $headers) : self
    {
        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this|HttpTrait|static
     */
    public function addHeader(string $name, string $value) : self
    {
        $name = $this->normalizeHeader($name);
        if (isset($this->headers[$name])) {
            throw new \InvalidArgumentException(
                sprintf("Header %s is already set with value '%s'", $name, $this->headers[$name])
            );
        }

        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this|HttpTrait|static
     */
    public function removeHeader(string $name) : self
    {
        if (!isset($this->headers[$name])) {
            return $this;
        }

        unset($this->headers[$name]);

        return $this;
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
     *
     * @return $this|HttpTrait|static
     */
    public function addCookie(Cookie $cookie) : self
    {
        if (isset($this->cookies[$cookie->getName()])) {
            throw new \InvalidArgumentException(sprintf("Cookie '%s' is allready set", $cookie->getName()));
        }

        $this->cookies[$cookie->getName()] = $cookie;

        return $this;
    }

    /**
     * @param Cookie $cookie
     *
     * @throws \InvalidArgumentException
     *
     * @return $this|HttpTrait|static
     */
    public function setCookie(Cookie $cookie) : self
    {
        $this->cookies[$cookie->getName()] = $cookie;

        return $this;
    }

    /**
     * @param Cookie $cookie
     *
     * @return $this|HttpTrait|static
     */
    public function clearCookie(Cookie $cookie) : self
    {
        $cookie->setExpire(1);

        $this->cookies[$cookie->getName()] = $cookie;

        return $this;
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
     *
     * @return $this|HttpTrait|static
     */
    public function setBody(string $body) : self
    {
        $this->body = $body;

        return $this;
    }
}
