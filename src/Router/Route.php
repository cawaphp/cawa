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

namespace Cawa\Router;

class Route
{
    /**
     * @param string $route
     *
     * @return static
     */
    public static function create(string $route = null) : self
    {
        $return = new static();

        if (is_string($route)) {
            $explode = explode(' >>> ', $route);
            if (sizeof($explode) == 2) {
                $return->setMatch($explode[0])
                    ->setController($explode[1]);
            }
        }

        return $return;
    }

    /**
     * @var int
     */
    private $responseCode;

    /**
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @param int $code
     *
     * @return $this
     */
    public function setResponseCode(int $code) : self
    {
        $this->responseCode = $code;

        return $this;
    }

    /**
     * @var string
     */
    private $name;

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name ? $this->name : (string) $this->responseCode;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @var string
     */
    private $match;

    /**
     * @return string
     */
    public function getMatch() : string
    {
        return $this->match;
    }

    /**
     * @param string $match
     *
     * @return static
     */
    public function setMatch(string $match) : self
    {
        if (substr($match, 0, 1) == '^' || substr($match, -1) == '$') {
            throw new \InvalidArgumentException(
                sprintf("Can't set start & end line on match for '%s'", $match)
            );
        }

        $this->match = $match;

        return $this;
    }

    /**
     * @var string
     */
    private $method;

    /**
     * @return string
     */
    public function getMethod()
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
     * @var UserInput[]
     */
    private $userInput = [];

    /**
     * @return UserInput[]
     */
    public function getUserInput() : array
    {
        return $this->userInput;
    }

    /**
     * @param UserInput[] $userInput
     *
     * @return $this
     */
    public function setUserInput(array $userInput) : self
    {
        $this->userInput = $userInput;

        return $this;
    }

    /**
     * @var callable
     */
    private $controller;

    /**
     * @return callable|string|array
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param callable|string|array $controller
     *
     * @return $this
     */
    public function setController($controller) : self
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Is the url will be transform (lowercase and space replace by -)
     */
    const OPTIONS_URLIZE = 'URLIZE';

    /**
     * Is the url will be cached, values is duration in sec
     */
    const OPTIONS_CACHE = 'CACHE';

    /**
     * @var array
     */
    private $options = [];

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getOption(string $name)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return null;
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
     * @return array
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options) : self
    {
        $this->options = $options;

        return $this;
    }
}
