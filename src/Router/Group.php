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

namespace Cawa\Router;

class Group extends AbstractRoute
{
    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * @return Route[]
     */
    public function getRoutes() : array
    {
        return $this->routes;
    }

    /**
     * @param Route[] $routes
     *
     * @return self|$this
     */
    public function setRoutes(array $routes) : self
    {
        $this->routes = $routes;

        return $this;
    }

    /**
     * @param string $match
     * @param string|null $name
     * @param array $routes
     */
    public function __construct(string $match = null, string $name = null, array $routes = [])
    {
        $this->match = $match;
        $this->name = $name;
        $this->routes = $routes;
    }
}
