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

use Cawa\Error\Formatter\CliFormatter;
use Cawa\Error\Formatter\HtmlFormatter;
use Cawa\VarDumper\CliDumper;
use Cawa\VarDumper\HtmlDumper;
use Cawa\VarDumper\VarCloner;
use Symfony\Component\VarDumper\VarDumper;

if (!function_exists('trace')) {
    /**
     * @param mixed $vars
     */
    function trace(... $vars)
    {
        static $init;

        if (!$init) {
            $cloner = new VarCloner();

            $isCurl = false;
            $isCli = 'cli' === PHP_SAPI;

            if (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'curl') !== false) {
                $isCli = true;
                $isCurl = true;
            }

            $dumper = $isCli ? new CliDumper() : new HtmlDumper();

            if ($isCurl) {
                $dumper::$defaultColors = true;
            }

            $handler = function($var) use ($cloner, $dumper) {
                $dumper->dump($cloner->cloneVar($var));
            };

            VarDumper::setHandler($handler);
        }

        if (sizeof($vars) == 1) {
            VarDumper::dump($vars[0]);
        } else {
            VarDumper::dump($vars);
        }
    }
}

if (!function_exists('systrace')) {
    /**
     * @param mixed $vars
     */
    function systrace(... $vars)
    {
        openlog('php-debug', LOG_PID, LOG_USER);

        $cloner = new VarCloner();
        $dumper = new \Symfony\Component\VarDumper\Dumper\CliDumper();
        $dumper::$defaultColors = true;
        $handler = function($var) use ($cloner, $dumper) {
            $dumper->dump($cloner->cloneVar($var), function($line, $depth, $indentPad) {
                syslog(LOG_DEBUG, str_repeat($indentPad, $depth < 0 ? 0 : $depth) . $line);
            });
        };
        $prevHandler = VarDumper::setHandler($handler);

        if (sizeof($vars) == 1) {
            VarDumper::dump($vars[0]);
        } else {
            VarDumper::dump($vars);
        }

        VarDumper::setHandler($prevHandler);
        closelog();
    }
}

if (!function_exists('backtrace')) {
    /**
     * @param string $name
     */
    function backtrace($name = null)
    {
        $isCli = 'cli' === PHP_SAPI;

        if (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'curl') !== false) {
            $isCli = true;
        }

        $formatter = $isCli ? new CliFormatter() : new HtmlFormatter();
        echo $formatter->backtrace($name);
    }
}
