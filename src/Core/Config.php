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

namespace Cawa\Core;

class Config
{
    /**
     * @var array
     */
    private $config;

    /**
     * Config constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @param array $config
     *
     * @return self|$this
     */
    public function add(array $config) : self
    {
        $this->config = array_replace_recursive($this->config, $config);

        return $this;
    }

    /**
     * @param string $path
     *
     * @return self|$this
     */
    public function load(string $path) : self
    {
        if (pathinfo($path, PATHINFO_EXTENSION) == 'yml') {
            $this->config = array_replace_recursive($this->config, yaml_parse_file($path));
        } else {
            $this->config = array_replace_recursive($this->config, require $path);
        }

        return $this;
    }

    /**
     * @param string $path
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function get(string $path)
    {
        $explode = explode('/', $path);
        $config = $this->config;
        while ($element = array_shift($explode)) {
            if (!isset($config[$element])) {
                throw new \InvalidArgumentException(
                    sprintf("Invalid configuration path '%s'", $path)
                );
            }
            $config = $config[$element];
        }

        return $config;
    }

    /**
     * @param string $path
     *
     * @return mixed|null
     */
    public function getIfExists(string $path)
    {
        try {
            return $this->get($path);
        } catch (\InvalidArgumentException $exception) {
            return null;
        }
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path) : bool
    {
        $explode = explode('/', $path);
        $config = $this->config;
        while ($element = array_shift($explode)) {
            if (!array_key_exists($element, $config)) {
                return false;
            }
            $config = $config[$element];
        }

        return true;
    }
}
