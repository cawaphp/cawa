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

class Route extends AbstractRoute
{
    /**
     * @param string $route
     *
     * @return $this
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
}
