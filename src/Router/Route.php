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

class Route extends AbstractRoute
{
    /**
     * @param string $route
     */
    public function __construct(string $route = null)
    {
        if (is_string($route)) {
            $explode = explode(' >>> ', $route);
            if (sizeof($explode) == 2) {
                $this->setMatch($explode[0])
                    ->setController($explode[1]);
            }
        }
    }

    /**
     * @var callable
     */
    private $controller;

    /**
     * @return callable|string|array|object
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param callable|string|array $controller
     *
     * @return $this|self
     */
    public function setController($controller) : self
    {
        $this->controller = $controller;

        return $this;
    }
}
