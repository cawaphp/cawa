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
     * @var float
     */
    private $previous;

    /**
     * Add timestamp/duration to given string
     *
     * @param string $message
     *
     * @return string
     */
    public function prefixWithTimestamp($message)
    {
        $diff = $this->previous ? round((microtime(true) - $this->previous), 3) : 0;
        $this->previous = microtime(true);

        $microtime = explode(' ', (string) microtime())[0];
        $microtime = substr((string) round($microtime, 3), 2, 3);
        $microtime = str_pad(is_bool($microtime) ? 0 : $microtime, 3, '0');

        $prefix = sprintf(
            '<fg=white>[%s.%s]</> <fg=yellow>[+%s s]</>',
            date('Y-m-d H:i:s'),
            $microtime,
            str_pad((string) $diff, 9, ' ', STR_PAD_LEFT)
        );
        $prefix = $this->getFormatter()->format($prefix);

        $message = str_pad($prefix, strlen($prefix) + 1) . $message;

        return $message;
    }
}
