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

class Request
{
    use HttpTrait;
    use ParameterTrait;

    /**
     * @param Uri|null $uri
     */
    public function __construct(Uri $uri = null)
    {
        $this->uri = $uri;
    }

    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    public function setMethod(string $method) : self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @var \Cawa\Net\Uri
     */
    protected $uri;

    /**
     * @return Uri
     */
    public function getUri() : Uri
    {
        return clone $this->uri;
    }

    /**
     * @var string
     */
    protected $payload;

    /**
     * @return string|null
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param string $payload
     *
     * @return $this
     */
    public function setPayload(string $payload) : self
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * @param array $input
     * @param string $name
     *
     * @return mixed
     */
    private function getUserData(array $input, string $name)
    {
        if (stripos($name, '[') !== false) {
            $names = explode('[', str_replace(']', '', $name));

            $ref = &$input;
            $leave = false;

            while ($leave == false) {
                $key = array_shift($names);

                if ($key === '') {
                    $leave = true;
                } elseif (is_null($key)) {
                    $leave = true;
                } else {
                    $ref = &$ref[$key];
                }
            }

            if (is_array($ref) && array_key_exists(0, $ref) && is_null($ref[0])) {
                $ref = [];
            }

            return $ref;
        } else {
            return $input[$name] ?? null;
        }
    }

    /**
     * @param string $name
     * @param string $type
     * @param mixed $default
     *
     * @return string|null
     */
    public function getQuery(string $name, string $type = null, $default = null)
    {
        $value = $this->getUserData($this->uri->getQueries(), $name);

        if ($type) {
            $value = $this->validateType($value, $type, $default);
        }

        return $value;
    }

    /**
     * @return array
     */
    public function getQueries()  : array
    {
        return $this->uri->getQueries();
    }

    /**
     * @var array
     */
    protected $post = [];

    /**
     * @param string $name
     * @param string $type
     * @param mixed $default
     *
     * @return string|null
     */
    public function getPost(string $name, string $type = null, $default = null)
    {
        $value = $this->getUserData($this->post, $name);

        if ($type) {
            $value = $this->validateType($value, $type, $default);
        }

        return $value;
    }

    /**
     * @param string $name
     * @param string|null $type
     * @param null $default
     *
     * @return null|string
     */
    public function getPostOrQuery(string $name, string $type = null, $default = null)
    {
        $post = $this->getPost($name, $type, $default);
        if (!is_null($post)) {
            return $post;
        }

        return $this->getQuery($name, $type, $default);
    }

    /**
     * @param string $name
     * @param string|array $value
     *
     * @return $this
     */
    public function setPost(string $name, $value) : self
    {
        $this->post[$name] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getPosts()  : array
    {
        return $this->post;
    }

    /**
     * @param array $posts
     *
     * @return $this
     */
    public function setPosts(array $posts) : self
    {
        $this->post = $posts;

        return $this;
    }
    /**
     * Get Querystring or Post data depending on request method
     *
     * @param string $name
     * @param string $type
     * @param mixed $default
     *
     * @return string|null
     */
    public function getArg(string $name, string $type = null, $default = null)
    {
        if ($this->method == 'POST') {
            return $this->getPost($name, $type, $default);
        } else {
            return $this->getQuery($name, $type, $default);
        }
    }

    /**
     * @var array
     */
    protected $files = [];

    /**
     * @param string $name
     *
     * @return File
     */
    public function getUploadedFile(string $name)
    {
        return $this->getUserData($this->files, $name);
    }

    /**
     * @return array
     */
    public function getUploadedFiles() : array
    {
        return $this->files;
    }

    /**
     * @return bool
     */
    public function isAjax() : bool
    {
        $value = $this->getHeader('X-Requested-With');

        if ($value && strtolower($value) == 'xmlhttprequest') {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getAcceptedLanguage() : array
    {
        if (!$this->getHeader('Accept-Language')) {
            return [];
        }

        $split = preg_split(
            '/\s*(?:,*("[^"]+"),*|,*(\'[^\']+\'),*|,+)\s*/',
            $this->getHeader('Accept-Language'),
            0,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );

        $locales = [];

        foreach ($split as $locale) {
            $explode = explode(';', $locale);
            if (isset($explode[1])) {
                $locales[$explode[0]] = (float) str_replace('q=', '', $explode[1]);
            } else {
                $locales[$explode[0]] = 1;
            }
        }

        uasort($locales, function ($a, $b) {

            return $a > $b ? -1 : 1;
        });

        return array_keys($locales);
    }
}
