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

use Cawa\Core\App;
use Cawa\Uri\Uri;

class ServerResponse extends Response
{
    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function addHeaderIfNotExist(string $name, string $value) : bool
    {
        if ($this->headersSent || headers_sent()) {
            throw new \LogicException('Headers is already sent');
        }

        return parent::addHeaderIfNotExist($name, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function addHeader(string $name, string $value) : bool
    {
        if ($this->headersSent || headers_sent()) {
            throw new \LogicException('Headers is already sent');
        }

        return parent::addHeader($name, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function removeHeader(string $name)
    {
        if ($this->headersSent || headers_sent()) {
            throw new \LogicException('Headers is already sent');
        }

        return parent::removeHeader($name);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function addCookie(Cookie $cookie)
    {
        if ($this->headersSent || headers_sent()) {
            throw new \LogicException('Headers is already sent');
        }

        return parent::addCookie($cookie);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function setCookie(Cookie $cookie)
    {
        if ($this->headersSent || headers_sent()) {
            throw new \LogicException('Headers is already sent');
        }

        return parent::setCookie($cookie);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function clearCookie(Cookie $cookie)
    {
        if ($this->headersSent || headers_sent()) {
            throw new \LogicException('Headers is already sent');
        }

        return parent::clearCookie($cookie);
    }

    /**
     * @param int $code
     * @param string $value
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function addStatusCode(int $code, string $value)
    {
        if (isset(self::$statusCodeList[$code])) {
            throw new \InvalidArgumentException(sprintf("Already defined status code '%s'", $code));
        }

        self::$statusCodeList[$code] = $value;

        return $this;
    }

    /**
     * @param int $code
     *
     * @return $this
     */
    public function setStatus(int $code) : self
    {
        if ($this->headersSent || headers_sent()) {
            throw new \LogicException('Headers is already sent');
        }

        $this->statusCode = $code;

        return $this;
    }

    /**
     * @param Uri|string $url
     * @param int $statusCode
     */
    public function redirect($url, int $statusCode = 302)
    {
        if (!$url instanceof Uri && !is_string($url)) {
            throw new \InvalidArgumentException(sprintf('Invalid redirect url with type %s', gettype($url)));
        }

        $this->setStatus($statusCode);
        $this->addHeader('Location', (string) $url);
        App::end();
    }

    /**
     * @param string $name
     * @param array $data
     * @param int $statusCode
     */
    public function redirectRoute($name, array $data = [], int $statusCode = 302)
    {
        $url = App::router()->getUri($name, $data);
        $this->setStatus($statusCode);
        $this->addHeader('Location', (string) $url);
        App::end();
    }

    /**
     * @var bool;
     */
    private $headersSent = false;

    /**
     * @return void
     */
    private function sendHeaders()
    {
        if ($this->headersSent || headers_sent()) {
            throw new \LogicException('Headers is already sent');
        }

        header('HTTP/1.1 ' . $this->statusCode . ' '  . self::$statusCodeList[$this->statusCode]);

        foreach ($this->cookies as $cookie) {
            setcookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpire(),
                $cookie->getPath(),
                $cookie->getDomain() ?? '',
                $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        $this->headersSent = true;
    }

    /**
     * @return string
     */
    public function send()
    {
        $this->sendHeaders();

        return $this->body;
    }
}
