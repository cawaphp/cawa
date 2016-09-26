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

namespace Cawa\Net;

use Pdp\Parser;
use Pdp\PublicSuffixListManager;

class Uri
{
    /**
     * @var array
     */
    private $uri;

    /**
     * Uri constructor.
     *
     * @param string $uri
     */
    public function __construct(string $uri = null)
    {
        if (is_null($uri)) {
            $this->getCurrentUrl();
        } else {
            $this->uri = parse_url($uri);

            /*
            if (!isset($this->uri['scheme'])) {
                throw new \InvalidArgumentException('Invalid uri without scheme');
            }
            */

            if (isset($this->uri['query'])) {
                $this->uri['query'] = $this->parseStr($this->uri['query']);
            } else {
                $this->uri['query'] = [];
            }
        }
    }

    /**
     * @return void
     */
    private function getCurrentUrl()
    {
        if (!isset($_SERVER['REQUEST_URI']) || !isset($_SERVER['HTTP_HOST'])) {
            return null;
        }

        $parseUrl = @parse_url($_SERVER['REQUEST_URI']);

        if (sizeof($parseUrl) == 0) {
            return null;
        }

        // we decode after parse in order to keep querystring special chars
        if (isset($parseUrl['path'])) {
            $parseUrl['path'] = urldecode($parseUrl['path']);
        }

        $parseUrl['scheme'] = 'http';
        $parseUrl['host'] = $_SERVER['HTTP_HOST'];

        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            if ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
                $parseUrl['scheme'] = 'https';
            }
        }

        /* apache + variants specific way of checking for https */
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) {
            $parseUrl['scheme'] = 'https';
        }

        /* nginx way of checking for https */
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === '443') {
            $parseUrl['scheme'] = 'https';
        }

        if ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
            $parseUrl['port'] = $_SERVER['SERVER_PORT'];
        }

        if (isset($parseUrl['query'])) {
            $parseUrl['query'] = $this->parseStr($parseUrl['query']);
        } else {
            $parseUrl['query'] = [];
        }

        $this->uri = $parseUrl;
    }

    /**
     * @param string $string
     *
     * @return array
     */
    private function parseStr(string $string) : array
    {
        // $string = str_replace("+", "%2B", $string);
        parse_str($string, $query);

        return $query;
    }

    /**
     * @param string $uri
     *
     * @return $this|self
     */
    public static function parse(string $uri = null) : self
    {
        $uri = new Uri($uri);

        return $uri;
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    public static function getAbsoluteUri(string $uri = null) : string
    {
        $uri = new Uri($uri);

        return $uri->get(false);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getScheme() : string
    {
        return $this->uri['scheme'];
    }

    /**
     * @param string $scheme
     *
     * @return $this|self
     */
    public function setScheme(string $scheme) : self
    {
        $this->uri['scheme'] = $scheme;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHttps() : bool
    {
        return $this->uri['scheme'] == 'https';
    }

    /**
     * @return string|null
     */
    public function getUser()
    {
        return isset($this->uri['user']) ? $this->uri['user'] : null;
    }

    /**
     * @param string $user
     *
     * @return $this|self
     */
    public function setUser(string $user) : self
    {
        $this->uri['user'] = $user;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword()
    {
        return isset($this->uri['pass']) ? $this->uri['pass'] : null;
    }

    /**
     * @param string $password
     *
     * @return $this|self
     */
    public function setPassword(string $password) : self
    {
        $this->uri['pass'] = $password;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHost()
    {
        return isset($this->uri['host']) ? $this->uri['host'] : null;
    }

    /**
     * @return string|null
     */
    public function getDomain()
    {
        if (!isset($this->uri['host'])) {
            return null;
        }

        $pslManager = new PublicSuffixListManager();
        $parser = new Parser($pslManager->getList());
        $parse = $parser->parseUrl($this->uri['host'] . '://' . $this->uri['host']);

        return $parse->host->registerableDomain;
    }

    /**
     * @param string $host
     *
     * @return $this|self
     */
    public function setHost(string $host) : self
    {
        $this->uri['host'] = $host;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPort()
    {
        return isset($this->uri['port']) ? $this->uri['port'] : null;
    }

    /**
     * @param int $port
     *
     * @return $this|self
     */
    public function setPort(int $port) : self
    {
        $this->uri['port'] = $port;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return string|null
     */
    public function getPath()
    {
        return isset($this->uri['path']) ? $this->uri['path'] : null;
    }

    /**
     * @param string $path
     *
     * @return $this|self
     */
    public function setPath(string $path) : self
    {
        $this->uri['path'] = $path;

        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this|self
     */
    public function appendPath(string $path) : self
    {
        $this->uri['path'] .= $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getQuerystring()
    {
        if (!empty($this->uri['query'])) {
            if (sizeof($this->uri['query']) == 1 && array_values($this->uri['query'])[0] == '') {
                return array_keys($this->uri['query'])[0];
            } else {
                return http_build_query($this->uri['query'], '', '&', PHP_QUERY_RFC3986);
            }
        }

        return null;
    }

    /**
     * @param string $query
     *
     * @return $this|self
     */
    public function setQuerystring(string $query = null) : self
    {
        if (is_null($query)) {
            unset($this->uri['query']);
        } else {
            $this->uri['query'] = $this->parseStr($query);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getQueries() : array
    {
        return $this->uri['query'];
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getQuery(string $name)
    {
        if (isset($this->uri['query'][$name])) {
            return $this->uri['query'][$name];
        }

        return null;
    }

    /**
     * @param array $query
     *
     * @return $this|self
     */
    public function setQueries(array $query = []) : self
    {
        if (sizeof($query) == 0) {
            unset($this->uri['query']);
        } else {
            $this->uri['query'] = $query;
        }

        return $this;
    }

    /**
     * Add query string to current url, overwrite the one that allready exists
     * This method is the only way to add an array as value for a querystring (non standard)
     *
     * @param array $queries
     *
     * @throws \InvalidArgumentException
     *
     * @return $this|self
     */
    public function addQueries(array $queries) : self
    {
        foreach ($queries as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            if (!is_string($key)) {
                throw new \InvalidArgumentException("Invalid querystring '" . $key . "'");
            }

            if (!is_string($value) && !is_array($value)) {
                throw new \InvalidArgumentException(sprintf(
                    "Invalid querystring value '%s' with type '%s'",
                    $key,
                    gettype($value)
                ));
            }

            $this->uri['query'][$key] = $value;
        }

        return $this;
    }

    /**
     * Add query string to current url, overwrite the one that allready exists
     *
     * @param string $key
     * @param string $value
     *
     * @return $this|self
     */
    public function addQuery(string $key, string $value) : self
    {
        $this->uri['query'][$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return $this|self
     */
    public function removeQuery(string $key) : self
    {
        if (isset($this->uri['query'][$key])) {
            unset($this->uri['query'][$key]);
        }

        return $this;
    }

    /**
     * Remove selected query string to current url
     *
     * @param array $queries
     *
     * @throws \InvalidArgumentException
     *
     * @return $this|self
     */
    public function removeQueries(array $queries) : self
    {
        foreach ($queries as $key) {
            if (!is_string($key)) {
                throw new \InvalidArgumentException("Invalid querystring '" . $key . "'");
            }

            if (isset($this->uri['query'][$key])) {
                unset($this->uri['query'][$key]);
            }
        }

        return $this;
    }

    /**
     * Remove all query string to current url
     *
     * @return $this|self
     */
    public function removeAllQueries() : self
    {
        $this->uri['query'] = [];

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFragment()
    {
        return isset($this->uri['fragment']) ? $this->uri['fragment'] : null;
    }

    /**
     * @param string $fragment
     *
     * @return $this|self
     */
    public function setFragment(string $fragment = null) : self
    {
        if (is_null($fragment)) {
            unset($this->uri['fragment']);
        } else {
            $this->uri['fragment'] = $fragment;
        }

        return $this;
    }

    /**
     * @param bool $auth
     *
     * @return string
     */
    public function getHostFull(bool $auth = false) : string
    {
        $out = (isset($this->uri['scheme']) ? $this->uri['scheme'] . '://' : '');

        if ($auth !== false && isset($this->uri['user'])) {
            $out .= $this->uri['user'] .
              (isset($this->uri['pass']) ? ':' . $this->uri['pass'] : '') . '@';
        }

        $out .= (isset($this->uri['host']) ? $this->uri['host'] : '') .
            (isset($this->uri['port']) ? ':' . $this->uri['port'] : '');

        return $out;
    }

    /**
     * @param bool $relative
     * @param bool $auth
     *
     * @return string|string
     */
    public function get(bool $relative = true, bool $auth = false): string
    {
        $out = '';
        if ($relative === false) {
            $out .= $this->getHostFull($auth);
        }

        $out .= (isset($this->uri['path']) ? $this->uri['path'] : '');

        if (!empty($this->uri['query'])) {
            $out .= '?' . $this->getQuerystring();
        }

        $out .= (isset($this->uri['fragment']) ? '#' . $this->uri['fragment'] : '');

        return $out;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->get();
    }
}
