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

namespace Cawa\Router;

abstract class AbstractRoute
{
    /**
     * Is the url will be transform (lowercase and space replace by -).
     */
    const OPTIONS_URLIZE = 'URLIZE';

    /**
     * Is the url will be cached, values is duration in sec.
     */
    const OPTIONS_CACHE = 'CACHE';

    /**
     * @var int
     */
    protected $responseCode;

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
     * @return $this|self
     */
    public function setResponseCode(int $code) : self
    {
        $this->responseCode = $code;

        return $this;
    }

    /**
     * @var string
     */
    protected $name;

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
     * @return $this|self
     */
    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @var string
     */
    protected $httpMethod;

    /**
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * @param string $httpMethod
     *
     * @return $this|self
     */
    public function setHttpMethod(string $httpMethod) : self
    {
        $this->httpMethod = $httpMethod;

        return $this;
    }

    /**
     * @var string
     */
    protected $match;

    /**
     * @return string|null
     */
    public function getMatch()
    {
        return $this->match;
    }

    /**
     * @param string $match
     *
     * @return $this|self
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
     * @var UserInput[]
     */
    protected $userInput = [];

    /**
     * @return UserInput[]
     */
    public function getUserInputs() : array
    {
        return $this->userInput;
    }

    /**
     * @param UserInput[] $userInput
     *
     * @return $this|self
     */
    public function setUserInputs(array $userInput) : self
    {
        $this->userInput = $userInput;

        return $this;
    }

    /**
     * @var array
     */
    protected $args = [];

    /**
     * @return array
     */
    public function getArgs() : array
    {
        return $this->args;
    }

    /**
     * @param array $args
     *
     * @return $this|self
     */
    public function setArgs(array $args) : self
    {
        $this->args = $args;

        return $this;
    }

    /**
     * @var callable[]
     */
    protected $conditions = [];

    /**
     * @return callable[]
     */
    public function getConditions() : array
    {
        return $this->conditions;
    }

    /**
     * @param callable $condition
     *
     * @return $this|self
     */
    public function addCondition(callable $condition) : self
    {
        $this->conditions[] = $condition;

        return $this;
    }

    /**
     * @param callable[] $conditions
     *
     * @return $this|self
     */
    public function setConditions(array $conditions) : self
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @var array
     */
    protected $options = [];

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
     * @return $this|self
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
     * @return $this|self
     */
    public function setOptions(array $options) : self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param Group $group
     */
    public function addGroupConfiguration(Group $group)
    {
        if ($group->getHttpMethod() && !$this->getHttpMethod()) {
            $this->setHttpMethod($group->getHttpMethod());
        }

        if ($group->getResponseCode() && !$this->getResponseCode()) {
            $this->setResponseCode($group->getResponseCode());
        }

        $this->options = array_merge($group->getOptions(), $this->options);
        $this->userInput = array_merge($group->getUserInputs(), $this->userInput);
        $this->conditions = array_merge($group->getConditions(), $this->conditions);
        $this->args = array_merge($group->getArgs(), $this->args);
    }
}
