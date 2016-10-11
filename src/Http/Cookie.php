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

use Cawa\Date\DateTime;

class Cookie
{
    /**
     * The name of the cookie
     *
     * @var string
     */
    protected $name;

    /**
     * Gets the name of the cookie.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this|self
     */
    public function setName(string $name) : self
    {
        // from PHP source code
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        if (empty($name)) {
            throw new \InvalidArgumentException('The cookie name cannot be empty.');
        };

        $this->name = $name;

        return $this;
    }

    /**
     * The value of the cookie
     *
     * @var string
     */
    protected $value;

    /**
     * Gets the value of the cookie.
     *
     * @return string
     */
    public function getValue() : string
    {
        return $this->value;
    }

    /**
     * Set the value of the cookie.
     *
     * @param string $value
     *
     * @return $this|self
     */
    public function setValue(string $value) : self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * The domain that the cookie is available to
     *
     * @var string
     */
    protected $domain;

    /**
     * Gets the domain that the cookie is available to.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set the domain that the cookie is available to.
     *
     * @param string $domain
     *
     * @return $this|self
     */
    public function setDomain(string $domain = null) : self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * The time the cookie expires
     *
     * @var int
     */
    protected $expire;

    /**
     * Gets the time the cookie expires.
     *
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * Set the time the cookie expires.
     *
     * @param int|\DateTime $expire if int, the ttl of this cookie, 0 if session cookie
     *
     * @throws \InvalidArgumentException
     *
     * @return $this|self
     */
    public function setExpire($expire) : self
    {
        // convert expiration time to a Unix timestamp
        if ($expire instanceof \DateTime) {
            $expire = $expire->format('U');
        } elseif (!is_numeric($expire) || !is_int($expire)) {
            throw new \InvalidArgumentException(sprintf(
                "The cookie expiration time is not valid with type '%s'",
                gettype($expire)
            ));
        } elseif ($expire > 0) {
            $expire = time() + $expire;
        }

        $this->expire = $expire;

        return $this;
    }

    /**
     * Whether this cookie is about to be cleared.
     *
     * @return bool
     */
    public function isCleared() : bool
    {
        return $this->expire < time();
    }

    /**
     * The path on the server in which the cookie will be available on
     *
     * @var string
     */
    protected $path;

    /**
     * Gets the path on the server in which the cookie will be available on.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the path on the server in which the cookie will be available on.
     *
     * @param string $path
     *
     * @return $this|self
     */
    public function setPath(string $path) : self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Whether the cookie should only be transmitted over a secure HTTPS connection from the client
     *
     * @var bool
     */
    protected $secure;

    /**
     * Checks whether the cookie should only be transmitted over a secure HTTPS connection from the client.
     *
     * @return bool
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * Set if the cookie should only be transmitted over a secure HTTPS connection from the client
     *
     * @param bool $secure
     *
     * @return $this|self
     */
    public function setSecure(bool $secure) : self
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * Whether the cookie will be made accessible only through the HTTP protocol
     *
     * @var bool
     */
    protected $httpOnly;

    /**
     * Checks whether the cookie will be made accessible only through the HTTP protocol.
     *
     * @return bool
     */
    public function isHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * Set if the cookie will be made accessible only through the HTTP protocol
     *
     * @param bool $httOnly
     *
     * @return $this|self
     */
    public function setHttpOnly(bool $httOnly) : self
    {
        $this->httpOnly = $httOnly;

        return $this;
    }

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $value
     * @param int|\DateTime $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $name,
        string $value = '',
        $expire = 0,
        string $path = '/',
        string $domain = null,
        bool $secure = false,
        bool $httpOnly = true
    ) {
        $this->setName($name)
            ->setValue($value)
            ->setDomain($domain)
            ->setExpire($expire)
            ->setPath($path)
            ->setSecure($secure)
            ->setHttpOnly($httpOnly);
    }

    /**
     * @param string $cookieString
     *
     * @return $this|self
     */
    public static function parse(string $cookieString) : self
    {
        /* @var Cookie $cookie */

        foreach (explode('; ', $cookieString) as $i => $param) {
            $explode = explode('=', $param);

            if ($i == 0) {
                $cookie = new Cookie($explode[0], $explode[1]);
                $cookie->setHttpOnly(false);
            } else {
                switch ($explode[0]) {
                    case 'expires':
                        $cookie->setExpire(new DateTime($explode[1]));
                        break;
                    case 'path':
                        $cookie->setPath($explode[1]);
                        break;
                    case 'domain':
                        $cookie->setDomain($explode[1]);
                        break;
                    case 'secure':
                        $cookie->setSecure(true);
                        break;
                    case 'httponly':
                        $cookie->setHttpOnly(true);
                        break;
                }
            }
        }

        return $cookie;
    }

    /**
     * Returns the cookie as a string.
     *
     * @return string The cookie
     */
    public function __toString()
    {
        $str = urlencode($this->getName()) . '=';

        if ((string) $this->getValue() === '') {
            $str .= 'deleted; expires=' . gmdate('D, d-M-Y H:i:s T', time() - 31536001);
        } else {
            $str .= urlencode($this->getValue());

            if ($this->getExpire() !== 0) {
                $str .= '; expires=' . gmdate('D, d-M-Y H:i:s T', $this->getExpire());
            }
        }

        if ($this->path) {
            $str .= '; path=' . $this->path;
        }

        if ($this->getDomain()) {
            $str .= '; domain=' . $this->getDomain();
        }

        if (true === $this->isSecure()) {
            $str .= '; secure';
        }

        if (true === $this->isHttpOnly()) {
            $str .= '; httponly';
        }

        return $str;
    }
}
