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

trait SerializableTrait
{
    /**
     * Recursive serialize
     *
     * @param object $object
     * @param string $className
     *
     * @return array
     */
    protected static function getSerializableData($object, string $className = null)
    {
        $data = ['@type' => get_class($object)];

        $reflectionClass = new \ReflectionClass($className ? $className : $object);

        // parent class
        $parent = $reflectionClass->getParentClass();
        if ($parent) {
            $parentData = self::getSerializableData($object, $parent->getName());
            if (count($parentData) > 0) {
                $data = array_merge($data, $parentData);
            }
        }

        // current $object
        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);
            $name = $property->getName();

            // optimization: only save value if not the default value
            $defaults = $reflectionClass->getDefaultProperties();
            if (array_key_exists($name, $defaults) && $defaults[$name] === $value) {
                continue;
            }

            if (is_array($value)) {
                foreach ($value as $key => $current) {
                    if (is_object($current)) {
                        $currentVal = self::getSerializableData($current);
                    } else {
                        $currentVal = $current;
                    }

                    $data[$name][$key] = $currentVal;
                }
            } elseif (is_object($value)) {
                $data[$name] = self::getSerializableData($value);
            } else {
                $data[$name] = $value;
            }
        }

        return $data;
    }

    /**
     * Recursive unserialize to get parent class property.
     *
     * @param object $object
     * @param array $cacheData
     * @param string $className
     *
     * @return array
     */
    protected static function unserializeData($object, &$cacheData, string $className = null) : array
    {
        $reflectionClass = new \ReflectionClass($className ? $className : $object);
        unset($cacheData['@type']);

        // parent class
        $parent = $reflectionClass->getParentClass();
        if ($parent) {
            self::unserializeData($object, $cacheData, $parent->getName());
        }

        foreach ($reflectionClass->getProperties() as $property) {
            $name = $property->getName();

            if (array_key_exists($name, $cacheData)) {
                $currentValue = $cacheData[$name];
                if (isset($cacheData[$name]['@type'])) {
                    $reflection = new \ReflectionClass($cacheData[$name]['@type']);

                    $parent = $reflection;
                    $internal = false;

                    while ($parent !== false) {
                        $internal = $parent->isInternal() ? true : $internal;
                        $parent = $parent->getParentClass();
                    }

                    if ($internal) {
                        $data = $cacheData[$name];
                        $currentType = $cacheData[$name]['@type'];
                        unset($data['@type']);

                        $serialize = preg_replace(
                            '`^a:`',
                            'O:' . strlen($currentType) . ':"' . $currentType . '":',
                            serialize($data)
                        );
                        $currentValue = unserialize($serialize);
                    } else {
                        $currentValue = $reflection->newInstanceWithoutConstructor();
                        self::unserializeData($currentValue, $cacheData[$name]);
                    }
                } elseif (is_array($currentValue)) {
                    $currentValue = [];
                    foreach ($cacheData[$name] as $key => $value) {
                        if (isset($value['@type'])) {
                            $reflection = new \ReflectionClass($value['@type']);
                            $currentValue[$key] = $reflection->newInstanceWithoutConstructor();
                            self::unserializeData($currentValue[$key], $value);
                        } else {
                            $currentValue[$key] = $value;
                        }
                    }
                }

                $property->setAccessible(true);
                $property->setValue($object, $currentValue);

                unset($cacheData[$name]);
            }
        }

        return $cacheData;
    }
}
