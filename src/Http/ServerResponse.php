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

use Cawa\App\HttpApp;
use Cawa\App\HttpFactory;
use Cawa\Net\Uri;
use Cawa\Router\RouterFactory;

class ServerResponse extends Response
{
    use RouterFactory;
    use HttpFactory;

    /**
     * @param string|null $file
     * @param int|null $line
     *
     * @return bool
     */
    public function headerSent(string &$file = null, int &$line = null) : bool
    {
        return headers_sent($file, $line);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function addHeaderIfNotExist(string $name, string $value) : parent
    {
        if ($this->headerSent($file, $line)) {
            throw new \LogicException(sprintf("Headers is already sent in '%s:%s'", $file, $line));
        }

        return parent::addHeaderIfNotExist($name, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function addHeader(string $name, string $value) : parent
    {
        if ($this->headerSent($file, $line)) {
            throw new \LogicException(sprintf("Headers is already sent in '%s:%s'", $file, $line));
        }

        return parent::addHeader($name, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function removeHeader(string $name) : parent
    {
        if ($this->headerSent($file, $line)) {
            throw new \LogicException(sprintf("Headers is already sent in '%s:%s'", $file, $line));
        }

        return parent::removeHeader($name);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function addCookie(Cookie $cookie) : parent
    {
        if ($this->headerSent($file, $line)) {
            throw new \LogicException(sprintf("Headers is already sent in '%s:%s'", $file, $line));
        }

        return parent::addCookie($cookie);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function setCookie(Cookie $cookie) : parent
    {
        if ($this->headerSent($file, $line)) {
            throw new \LogicException(sprintf("Headers is already sent in '%s:%s'", $file, $line));
        }

        return parent::setCookie($cookie);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function clearCookie(Cookie $cookie) : parent
    {
        if ($this->headerSent($file, $line)) {
            throw new \LogicException(sprintf("Headers is already sent in '%s:%s'", $file, $line));
        }

        return parent::clearCookie($cookie);
    }

    /**
     * @param int $code
     * @param string $value
     *
     * @throws \InvalidArgumentException
     *
     * @return $this|self
     */
    public function addStatusCode(int $code, string $value) : self
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
     * @return $this|self
     */
    public function setStatus(int $code) : self
    {
        if ($this->headerSent($file, $line)) {
            throw new \LogicException(sprintf("Headers is already sent in '%s:%s'", $file, $line));
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
        HttpApp::instance()->end();
    }

    /**
     * @param string $name
     * @param array $data
     * @param int $statusCode
     */
    public function redirectRoute($name, array $data = [], int $statusCode = 302)
    {
        $url = self::uri($name, $data);
        $this->setStatus($statusCode);
        $this->addHeader('Location', (string) $url);
        HttpApp::instance()->end();
    }

    /**
     * @param int $statusCode
     */
    public function redirectSelf(int $statusCode = 302)
    {
        $this->setStatus($statusCode);
        $this->addHeader('Location', (string) self::request()->getUri());
        HttpApp::instance()->end();
    }

    /**
     */
    private function sendHeaders()
    {
        if ($this->headerSent($file, $line)) {
            throw new \LogicException(sprintf("Headers is already sent in '%s:%s'", $file, $line));
        }

        header('HTTP/1.1 ' . $this->statusCode . ' ' . self::$statusCodeList[$this->statusCode]);

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
