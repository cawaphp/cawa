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

namespace Cawa\Orm;

abstract class Model extends Serializable
{
    /**
     * @var array
     */
    protected $changedProperties = [];

    /**
     * @return array
     */
    public function getChangedProperties() : array
    {
        return $this->changedProperties;
    }

    /**
     * @param string $property
     * @param mixed $value
     */
    protected function addChangedProperties(string $property, $value)
    {
        $this->changedProperties[$property] = $value;
    }

    /**
     * @return bool
     */
    protected function hasChangedProperties() : bool
    {
        return sizeof($this->changedProperties) > 0;
    }

    /**
     * @return void
     */
    public function resetChangedProperties()
    {
        $this->changedProperties = [];
    }

    /**
     * @param array $result
     */
    abstract public function map(array $result);
}
