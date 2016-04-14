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

/*
 * Сáша frameworks
 *
 * @author tchiotludo <http://github.com/tchiotludo>
 */
use Cawa\App\App;
use Cawa\Error\Formatter\HtmlFormatter;
use Cawa\VarDumper\CliDumper;
use Cawa\VarDumper\HtmlDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\VarDumper;

if (!function_exists('trace')) {
    /**
     * @param mixed $var
     */
    function trace(... $vars)
    {
        static $init;

        if (!$init) {
            $cloner = new VarCloner();

            if (App::isInit()) {
                $isCli = false;
            } else {
                $isCli = 'cli' === PHP_SAPI;
            }

            $dumper = $isCli ? new CliDumper() : new HtmlDumper();
            $handler = function ($var) use ($cloner, $dumper) {
                $dumper->dump($cloner->cloneVar($var));
            };

            VarDumper::setHandler($handler);
        }

        foreach ($vars as $var) {
            VarDumper::dump($var);
        }
    }
}

if (!function_exists('traceDie')) {
    /**
     * @param mixed $var
     */
    function traceDie(... $vars)
    {
        while (ob_get_level() > 1) {
            ob_get_clean();
        }

        foreach ($vars as $var) {
            trace($var);
        }
        exit();
    }
}

if (!function_exists('backtrace')) {
    /**
     * @param string $name
     */
    function backtrace($name = null)
    {
        $formatter = new HtmlFormatter();

        echo $formatter->backtrace($name);
    }
}
