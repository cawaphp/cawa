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

namespace Cawa\Core;

abstract class DI
{
    /**
     * @var array
     */
    private static $container = [];

    /**
     * @param string $namespace
     * @param string $configPath
     * @param string $name
     * @param bool $strict
     *
     * @return array
     */
    public static function detect(string $namespace, string $configPath, string $name = null, bool $strict = true) : array
    {
        $configName = null;
        $method = $strict ? 'get' : 'getIfExists';

        if (is_null($name)) {
            $configName = 'default';
        } elseif (class_exists($name)) {
            $all = self::config()->$method($configPath);
            foreach ($all ?? [] as $key => $value) {
                if (stripos($name, $key) === 0) {
                    $configName = $key;
                    break;
                }
            }
        } elseif (is_string($name)) {
            $configName = $name;
        }

        if (is_null($configName)) {
            if ($strict) {
                throw new \RuntimeException(sprintf(
                    "Can't detect configuration for namespace: '%s', config: '%s', type: '%s'",
                    $namespace,
                    $configPath,
                    is_string($name) ? $name : get_class($name)
                ));
            } else {
                $configName = 'default';
            }
        }

        if ($strict == false && is_null($configName)) {
            if ($return = self::get($namespace, $name)) {
                return [null, null, $return];
            }

            return [$name, null, $return];
        }

        if ($return = self::get($namespace, $configName)) {
            return [null, null, $return];
        }

        $config = self::config()->$method($configPath . '/' . $configName);

        if ($config) {
            return [$configName, $config, null];
        } elseif ($strict == false) {
            return [$configName ?: $name, null, null];
        }
    }

    /**
     * @param string $namespace
     * @param string $name
     *
     * @return mixed|string
     */
    public static function get(string $namespace, string $name = null)
    {
        $name = $name ?: 'default';

        if (isset(self::$container[$namespace][$name])) {
            return self::$container[$namespace][$name];
        }

        return null;
    }

    /**
     * @param string $namespace
     * @param string $name
     * @param object $object
     *
     * @return mixed
     */
    public static function set(string $namespace, string $name = null, $object = null)
    {
        $name = $name ?: 'default';

        self::$container[$namespace][$name] = $object;

        return $object;
    }

    /**
     * @var Config
     */
    private static $config;

    /**
     * @return Config
     */
    public static function config() : Config
    {
        if (!self::$config) {
            self::$config = new Config();
        }

        return self::$config;
    }
}
