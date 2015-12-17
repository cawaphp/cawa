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
     */
    public function add(array $config)
    {
        $this->config = array_merge($this->config, $config);
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
}
