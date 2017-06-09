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

class UserInput
{
    /**
     * @var string
     */
    private $name;

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @var string
     */
    private $type;

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @var bool
     */
    private $mandatory;

    /**
     * @return bool
     */
    public function isMandatory() : bool
    {
        return $this->mandatory;
    }

    /**
     * @param string $name
     * @param string $type
     * @param bool $mandatory
     */
    public function __construct(string $name, string $type, bool $mandatory = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->mandatory = $mandatory;
    }
}
