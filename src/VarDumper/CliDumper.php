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

class CliDumper extends \Symfony\Component\VarDumper\Dumper\CliDumper
{
    use DumperTrait;

    /**
     * {@inheritdoc}
     */
    protected function echoLine($line, $depth, $indentPad)
    {
        if (-1 !== $depth) {
            echo str_repeat($indentPad, $depth) . $line . "\n";
        }
    }

    /**
     * @var int
     */
    protected $lastDepth = -1;

    /**
     * {@inheritdoc}
     */
    protected function dumpLine($depth, $endOfValue = false)
    {
        if (-1 === $this->lastDepth && isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            if ($this->colors) {
                echo sprintf("\033[%sm%s\033[m", $this->styles['ref'], $this->prefix());
            } else {
                echo $this->prefix();
            }
            echo "\n";
        }

        $this->lastDepth = $depth;

        parent::dumpLine($depth, $endOfValue);
    }
}
