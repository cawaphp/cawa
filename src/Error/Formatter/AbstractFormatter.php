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

namespace Cawa\Error\Formatter;

use Cawa\App\AbstractApp;
use ErrorException;

abstract class AbstractFormatter
{
    /**
     * @param \Throwable $exception
     *
     * @return array
     */
    protected function exceptionStackTrace(\Throwable $exception) : array
    {
        $return = [];
        $line = $exception->getLine();
        $file = $exception->getFile();

        $trace = $exception->getTrace();
        foreach (explode("\n", $exception->getTraceAsString()) as $currentLine) {
            $row = explode(' ', $currentLine);

            $stack = ['file' => null];
            $buffer = null;
            $index = trim(array_shift($row), '#');

            foreach ($row as $current) {
                // current file
                if (is_null($stack['file'])) {
                    if (substr($current, -1) === ':') {
                        $stack['file'] = trim($buffer ? $buffer . $current : $current, ':');

                        if (preg_match('`([^\\(]+)\\(([0-9]+)\\)`', $stack['file'], $matches)) {
                            $stack['file'] = $matches[1];
                            $stack['line'] = (int) $matches[2];
                        }
                    } else {
                        $buffer .= $current . ' ';
                    }
                } elseif (!isset($stack['function'])) {
                    $stack['function'] = strstr($current, '(', true);
                    $explodeString = strpos($stack['function'], '::') !== false ? '::' : '->';
                    $explode = explode($explodeString, $stack['function']);

                    if (sizeof($explode) > 1) {
                        $stack['class'] = $explode[0];
                        $stack['type'] = $explodeString;
                        $stack['function'] = $explode[1];

                        if (strpos($stack['class'], '\\') !== false) {
                            $explode = explode('\\', $stack['class']);
                            $stack['class'] = array_pop($explode);
                            $stack['namespace'] = implode('\\', $explode);
                        }
                    } elseif (strpos($stack['function'], '\\') !== false) {
                        $explode = explode('\\', $stack['function']);
                        $stack['function'] = array_pop($explode);
                        $stack['namespace'] = implode('\\', $explode);
                    }

                    $stack['args'] = strstr($current, '(') . ' ';
                } else {
                    $stack['args'] .= $current . ' ';
                }
            }

            if (is_null($stack['file'])) {
                $stack['file'] = $row[0];
            }

            $skip = false;

            // hack to hide error handler
            if (isset($stack['namespace']) && isset($stack['class'])) {
                if ($stack['namespace'] == 'Cawa\Error' && $stack['class'] == 'Handler') {
                    $skip = true;
                }
            }

            // hack to hide backtrace call
            if (isset($stack['function']) && $stack['function'] == 'backtrace') {
                $skip = true;
                $file = $stack['file'];
                $line = (int) $stack['line'];
            }

            if ($skip == false) {
                if (isset($trace[$index]) && isset($trace[$index]['args'])) {
                    $stack['fullargs'] = $trace[$index]['args'];
                }

                $return[] = $stack;
            }
        }

        $return[0]['line'] = $line;
        $return[0]['file'] = $file;

        return $return;
    }

    /**
     * @param array $stack
     *
     * @return string
     */
    protected function getType(array $stack)
    {
        if (!isset($stack['function'])) {
            return null;
        }

        if (isset($stack['namespace']) && isset($stack['class'])) {
            return $stack['namespace'] . '\\' . $stack['class'] . $stack['type'] . $stack['function'];
        } elseif (isset($stack['namespace'])) {
            return $stack['namespace'] . '\\' . $stack['function'];
        } else {
            return $stack['function'];
        }
    }

    /**
     * @param string|null $name
     *
     * @return string
     */
    public function backtrace(string $name = null) : string
    {
        $exception = new ErrorException(
            $name ?? 'backtrace',
            0,
            1,
            debug_backtrace()[0]['file'],
            (int) debug_backtrace()[0]['line']
        );

        return $this->render($exception, 1);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected static function mapPath(string $path) : string
    {
        if (getenv('APP_MAPPING')) {
            $path = str_replace(AbstractApp::getAppRoot(), getenv('APP_MAPPING'), $path);
        }

        return $path;
    }

    /**
     * @param string $path
     * @param int $line
     *
     * @return string
     */
    public static function getIdeLink(string $path, int $line = null) : ?string
    {
        $fileLinkFormat = ini_get('xdebug.file_link_format') ?: 'phpstorm://%f:%l';

        if ($path != '[internal function]' && $path != '{main}') {
            $path = self::mapPath($path);

            return str_replace(
                ['%f', '%l'],
                [$path, $line ?? 1],
                $fileLinkFormat
            );
        }

        return null;
    }

    /**
     * @param \Throwable $exception
     * @param int $index
     *
     * @return string
     */
    abstract public function render(\Throwable $exception, int $index) : string;
}
