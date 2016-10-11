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

namespace Cawa\VarDumper;

trait DumperTrait
{
    /**
     * @var float
     */
    protected static $lastTime;

    /**
     * {@inheritdoc}
     */
    protected function prefix()
    {
        $time = microtime(true);
        $fromStart = round(($time - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 3);

        if (self::$lastTime) {
            $fromLast = round(($time - self::$lastTime) * 1000, 3);
        } else {
            $fromLast = 0;
        }
        self::$lastTime = $time;

        $from = null;
        $extract = null;
        $backtraces = array_reverse(debug_backtrace());
        foreach ($backtraces as $backtrace) {
            if (isset($backtrace['function']) && $backtrace['function'] == 'trace') {
                $from = ' : ' . $backtrace['file'] . ':' . $backtrace['line'];

                // replace % in order to avoid sprintf failure
                $extract = "\n" .
                    str_replace('%', '&#' . ord('%') . ';', trim(file($backtrace['file'])[$backtrace['line'] - 1])) .
                    "\n";
            }
        }

        if ($extract && $this instanceof HtmlDumper) {
            $extract = '<span class="sf-dump-ref">' . $extract . '</span>';
        }

        if ($fromLast) {
            $return = '' . $fromStart . "ms (+ $fromLast ms)" . $from;
        } else {
            $return = '' . $fromStart . 'ms (+ 0.000 ms)' . $from;
        }

        if ($this instanceof HtmlDumper) {
            $return = '<span class="sf-dump-index ">' . $return . '</span>';
        }

        return $return . $extract;
    }
}
