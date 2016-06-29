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

namespace Cawa\Serializer;

class Serializer
{
    /**
     * @var object[]
     */
    private $references = [];

    /**
     * @var object[]
     */
    private $referencesUsed = [];

    /**
     * @private
     */
    private function __construct()
    {
    }

    /**
     * @param object $object
     *
     * @return array
     */
    public static function serialize($object) : array
    {
        $serializer = new static();
        $data = $serializer->serializeObject($object);

        if (sizeof($serializer->referencesUsed)) {
            foreach ($serializer->referencesUsed as $index) {
                $data['@references'][$index] = $serializer->references[$index];
            }
        }

        return $data;
    }

    /**
     * @param object $object
     * @param array $data
     *
     * @return object
     */
    public static function unserialize($object, array $data)
    {
        $serializer = new static();
        $serializer->references = $data['@references'] ?? [];
        unset($data['@references']);

        return $serializer->unserializeObject($object, $data);
    }

    /**
     * Recursive serialize
     *
     * @param object $object
     * @param string $className
     *
     * @return array
     */
    private function serializeObject($object, string $className = null)
    {
        if (is_null($className)) {
            $index = array_search($object, $this->references, true);

            if ($index !== false) {
                $this->referencesUsed[] = $index;

                return ['@ref' => $index];
            }

            $this->references[] = $object;
        }

        $data = ['@type' => get_class($object)];

        $reflectionClass = new \ReflectionClass($className ? $className : $object);

        // parent class
        $parent = $reflectionClass->getParentClass();
        if ($parent) {
            $parentData = $this->serializeObject($object, $parent->getName());
            if (count($parentData) > 0) {
                $data = array_merge($data, $parentData);
            }
        }

        // current $object
        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);
            $name = $property->getName();

            // for parent class, don't reserialize public & protected properties
            if (isset($data[$name])) {
                continue;
            }

            // optimization: only save value if not the default value
            $defaults = $reflectionClass->getDefaultProperties();
            if (array_key_exists($name, $defaults) && $defaults[$name] === $value) {
                continue;
            }

            $this->serializeValue($name, $value, $data);
        }

        // ugly hack for public undeclared properties and
        // for internal object like datetime that can't be accessed by reflection
        // @see http://news.php.net/php.internals/93826%20view%20original
        if (!$className) {
            foreach (get_object_vars($object) as $key => $value) {
                if (!isset($data[$key])) {
                    $this->serializeValue($key, $value, $data);
                }
            }
        }

        return $data;
    }

    /**
     * @param string $name
     * @param $value
     * @param array $data
     */
    private function serializeValue(string $name, $value, array &$data)
    {
        if (is_array($value)) {
            foreach ($value as $key => $current) {
                if (is_object($current)) {
                    $currentVal = $this->serializeObject($current);
                } else {
                    $currentVal = $current;
                }

                $data[$name][$key] = $currentVal;
            }
        } elseif (is_object($value)) {
            $data[$name] = $this->serializeObject($value);
        } else {
            $data[$name] = $value;
        }
    }

    /**
     * Recursive unserialize to get parent class property.
     *
     * @param object $object
     * @param array $data
     * @param string $className
     *
     * @return array
     */
    private function unserializeObject($object, &$data, string $className = null) : array
    {
        $reflectionClass = new \ReflectionClass($className ? $className : $object);
        unset($data['@type']);

        // parent class
        $parent = $reflectionClass->getParentClass();
        if ($parent) {
            $this->unserializeObject($object, $data, $parent->getName());
        }

        foreach ($reflectionClass->getProperties() as $property) {
            $name = $property->getName();

            if (array_key_exists($name, $data)) {
                $currentValue = $data[$name];

                if (isset($data[$name]['@ref'])) {
                    $currentValue = $this->references[$data[$name]['@ref']];
                } elseif (isset($data[$name]['@type'])) {
                    $reflection = new \ReflectionClass($data[$name]['@type']);

                    $parent = $reflection;
                    $internal = false;

                    while ($parent !== false) {
                        $internal = $parent->isInternal() ? true : $internal;
                        $parent = $parent->getParentClass();
                    }

                    if ($internal) {
                        $currentData = $data[$name];
                        $currentType = $data[$name]['@type'];
                        unset($currentData['@type']);

                        $serialize = preg_replace(
                            '`^a:`',
                            'O:' . strlen($currentType) . ':"' . $currentType . '":',
                            serialize($currentData)
                        );
                        $currentValue = unserialize($serialize);
                    } else {
                        $currentValue = $reflection->newInstanceWithoutConstructor();
                        $this->unserializeObject($currentValue, $data[$name]);
                    }
                } elseif (is_array($currentValue)) {
                    $currentValue = [];
                    foreach ($data[$name] as $key => $value) {
                        if (isset($value['@type'])) {
                            $reflection = new \ReflectionClass($value['@type']);
                            $currentValue[$key] = $reflection->newInstanceWithoutConstructor();
                            $this->unserializeObject($currentValue[$key], $value);
                        } else {
                            $currentValue[$key] = $value;
                        }
                    }
                }

                $property->setAccessible(true);
                $property->setValue($object, $currentValue);

                unset($data[$name]);
            }
        }

        return $data;
    }
}
