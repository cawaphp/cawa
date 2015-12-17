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

namespace Cawa\VarDumper;

class CliDumper extends \Symfony\Component\VarDumper\Dumper\CliDumper
{
    use Dumper;

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
                echo sprintf("\033[%sm%s\033[m", $this->styles['default'], $this->prefix());
            } else {
                echo $this->prefix();
            }
            echo "\n";
        }

        $this->lastDepth = $depth;

        parent::dumpLine($depth, $endOfValue);
    }
}
