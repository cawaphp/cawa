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
     *
     * @return array
     */
    private static function getSerializableData($object)
    {
        $data = ['@type' => get_class($object)];

        $reflectionClass = new \ReflectionObject($object);

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
     *
     * @return array
     */
    protected static function unserializeData($object, &$cacheData) : array
    {
        $reflectionClass = new \ReflectionObject($object);
        unset($cacheData['@type']);

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
                        $serialize = preg_replace(
                            '|^O:\d+:"\w+":|',
                            'O:' . strlen($cacheData[$name]['@type']) . ':"' . $cacheData[$name]['@type'] . '":',
                            serialize($cacheData[$name])
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
