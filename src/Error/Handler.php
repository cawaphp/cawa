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

namespace Cawa\Error;

use Cawa\Core\App;
use Cawa\Error\Exceptions\Deprecated;
use Cawa\Error\Exceptions\Error;
use Cawa\Error\Exceptions\Fatal;
use Cawa\Error\Exceptions\Notice;
use Cawa\Error\Exceptions\Warning;
use Cawa\Error\Formatter\HtmlFormatter;
use Cawa\Net\Ip;

class Handler
{
    /**
     * Friendly name from error code
     */
    const LEVEL_NAME = [
        E_ERROR => 'Error',
        E_PARSE => 'Parse Error',
        E_CORE_ERROR => 'Core Error',
        E_COMPILE_ERROR => 'Compile Error',
        E_USER_ERROR => 'User Error',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_NOTICE => 'Notice',
        E_USER_NOTICE => 'User Notice',
        E_WARNING => 'Warning',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_WARNING => 'User Warning',
        E_STRICT => 'Strict',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
    ];

    /**
     * Log level map from error code
     */
    const LEVEL_LOG = [
        'emergency' => [0, E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR],
        'error' => [E_NOTICE, E_USER_NOTICE],
        'alert' => [E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING],
        'warning' => [E_STRICT, E_DEPRECATED, E_USER_DEPRECATED],
    ];

    /**
     * Exception class from error code
     */
    const LEVEL_CLASS = [
        'Error' => [0, E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR],
        'Notice' => [E_NOTICE, E_USER_NOTICE],
        'Warning' => [E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING],
        'Deprecated' => [E_STRICT, E_DEPRECATED, E_USER_DEPRECATED],
    ];

    /**
     * @var string
     */
    private static $reservedMemory;

    /**
     * @return void
     */
    public static function register()
    {
        ini_set('display_errors', 'Off');

        if (self::$reservedMemory === null) {
            self::$reservedMemory = str_repeat('x', 10240);
            register_shutdown_function(__CLASS__ . '::fatalHandler');
        }

        set_error_handler([__CLASS__, 'errorHandler']);
        set_exception_handler([__CLASS__, 'exceptionHandler']);
        ini_set('unserialize_callback_func', 'Cawa\Error\Handler::unserializeHandler');
    }

    /**
     * Catch __PHP_Incomplete_Class
     *
     * @param string $className
     *
     * @throws \Exception
     */
    public static function unserializeHandler(string $className)
    {
        throw new \Exception(sprintf("Unable to unserialize class '%s'", $className));
    }

    /**
     * Catch uncatchable error (parse / compile / ...)
     */
    public static function fatalHandler()
    {
        if (null === self::$reservedMemory) {
            return;
        }

        self::$reservedMemory = null;

        $error = error_get_last();
        if ($error &&
            isset($error['type']) &&
            in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR]) &&
            isset($error['message'][0])
        ) {
            $exception = new Fatal($error['message'], 0, $error['type'], $error['file'], $error['line']);
            self::exceptionHandler($exception);
        }
    }

    /**
     * Convert old php error to exception
     *
     * @param int $errno
     * @param string $message
     * @param string $filename
     * @param string $linenumber
     *
     * @throws Error
     *
     * @return bool
     */
    public static function errorHandler($errno, $message = null, $filename = null, $linenumber = null)
    {
        // This error code is not included in error_reporting
        if (!error_reporting()) {
            return;
        }

        $class = 'Error';
        foreach (self::LEVEL_CLASS as $current => $codes) {
            if (in_array($errno, $codes) === true) {
                $class = $current;
            }
        }
        $class = 'Cawa\\Error\\Exceptions\\' . $class;

        $message = sprintf('[#%s %s] %s', $errno, self::LEVEL_NAME[$errno], $message);
        $oException = new $class($message, $errno, 0, $filename, $linenumber);

        throw $oException;
    }

    /**
     * @param \Throwable $exception
     *
     * @return bool
     */
    public static function exceptionHandler(\Throwable $exception)
    {
        // This error code is not included in error_reporting
        if (!error_reporting() || $exception->getLine() == 0) {
            return;
        }

        if (App::isInit()) {
            self::log($exception);

            App::response()->setStatus(500);

            if (App::env() != App::PROD || App::isAdminIp()) {
                $formatter = new HtmlFormatter();
                echo $formatter->render($exception);
            } else {
                self::clearAllBuffer();
                trace('Oups');
            }

            App::end();
        } else {
            if (!headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
            }

            if (App::env() != App::PROD) {
                $formatter = new HtmlFormatter();
                echo $formatter->render($exception);
            } else {
                self::clearAllBuffer();
                echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">' . "\n" .
                    '<html><head>' . "\n" .
                    '<title>500 Internal Server Error</title>' . "\n" .
                    '</head><body>' . "\n" .
                    '<h1>Internal Server Error</h1>' . "\n" .
                    '</body></html>';
            }
        }
    }

    /**
     * @param \Throwable $exception
     */
    public static function log(\Throwable $exception)
    {
        $level = 'emergency';
        if ($exception instanceof Error) {
            foreach (self::LEVEL_LOG as $log => $codes) {
                if (in_array($exception->getCode(), $codes) === true) {
                    $level = $log;
                }
            }
        }

        $context = [];
        $reflection = new \ReflectionClass($exception);
        foreach ($reflection->getProperties() as $property) {
            if (!$property->isPrivate()) {
                $property->setAccessible(true);
                $value = $property->getValue($exception);

                // can be exported as context
                if ($value === null || is_scalar($value) || is_callable([$value, '__toString'])) {
                    $context[$property->getName()] = (string) $value;
                }
            }
        }
        unset($context['message']);

        $start = App::request()->getServer('REQUEST_TIME_FLOAT');
        $end = microtime(true);
        $context['Duration'] = round(($end - $start) * 1000, 3);

        $context['Ip'] = Ip::get();
        $context['Url'] = App::request()->getUri()->get(false);
        $context['Trace'] = $exception->getTraceAsString();
        $context['Referer'] = App::request()->getHeader('Referer');

        App::logger()->log($level, $exception->getMessage(), $context);
    }

    /**
     * Clear all buffer to display error page
     */
    private static function clearAllBuffer()
    {
        while (ob_get_level() > 1) {
            ob_get_clean();
        }
    }
}
