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

namespace Cawa\Console;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutput as BaseConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class ConsoleOutput extends BaseConsoleOutput implements ConsoleOutputInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        $verbosity = self::VERBOSITY_NORMAL,
        $decorated = null,
        OutputFormatterInterface $formatter = null
    ) {
        parent::__construct($verbosity, $decorated, $formatter);
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($message, $newline)
    {
        if (trim($message) != '') {
            $message = $this->prefixWithTimestamp($message);
        }

        parent::doWrite($message, $newline);
    }

    /**
     * Add timestamp/duration to given string
     *
     * @param string $message
     *
     * @return string
     */
    public function prefixWithTimestamp($message)
    {
        $microtime = explode(' ', (string) microtime())[0];
        $microtime = substr((string) round($microtime, 3), 2, 3);
        $microtime = str_pad($microtime, 3, '0');

        $prefix = sprintf('<fg=white>[%s.%s]</>', date('Y-m-d H:i:s'), $microtime);
        $prefix = $this->getFormatter()->format($prefix);

        $message = str_pad($prefix, strlen($prefix) + 1) . $message;

        return $message;
    }
}
