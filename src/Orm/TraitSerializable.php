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

trait TraitSerializable
{
    /**
     * Recursive serialize to get parent class property.
     *
     * @param string|object $class
     * @param string $className
     *
     * @return array
     */
    private function recursiveSerialize($class, $className = null)
    {
        $data = [];
        $reflectionClass = new \ReflectionClass($className ? $className : $class);

        // parent class
        $parent = $reflectionClass->getParentClass();
        if ($parent) {
            $parentData = $this->recursiveSerialize($class, $parent->getName());
            if (count($parentData) > 0) {
                $data = array_merge($data, $parentData);
            }
        }

        // current class
        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($class);

            if (is_null($value)) {
                // optimization: only save null if not the default value
                if (!is_null($reflectionClass->getDefaultProperties()[$property->getName()])) {
                    $data[$property->getName()] = $value;
                }
            } else {
                $data[$property->getName()] = $value;
            }
        }

        return $data;
    }

    /**
     * Recursive unserialize to get parent class property.
     *
     * @param string|Model $className
     * @param array        $cacheData
     *
     * @return array
     */
    protected function recursiveUnserialize($className, $cacheData) : array
    {
        $reflectionClass = new \ReflectionClass($className);

        // parent class
        $parent = $reflectionClass->getParentClass();

        if ($parent) {
            $parentData = $this->recursiveUnserialize($parent->getName(), $cacheData);
            if (count($parentData) > 0) {
                $cacheData = array_merge($cacheData, $parentData);
            }
        }

        foreach ($reflectionClass->getProperties() as $property) {
            if (array_key_exists($property->getName(), $cacheData)) {
                $property->setAccessible(true);
                $property->setValue($this, $cacheData[$property->getName()]);
                unset($cacheData[$property->getName()]);
            }
        }

        return $cacheData;
    }
}
