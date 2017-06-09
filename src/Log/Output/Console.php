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

namespace Cawa\Log\Output;

use Cawa\Log\Event;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Console extends AbstractOutput
{
    /**
     * @var ConsoleOutputInterface
     */
    protected $output;

    /**
     * @param ConsoleOutputInterface $output
     */
    public function __construct(ConsoleOutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    protected function send(Event $event) : bool
    {
        $out = $this->output;

        switch ($event->getLevel()) {
            case LogLevel::DEBUG:
                $options = OutputInterface::VERBOSITY_DEBUG;
                break;

            case LogLevel::INFO:
                $options = OutputInterface::VERBOSITY_VERY_VERBOSE;
                break;

            case LogLevel::NOTICE:
                $options = OutputInterface::VERBOSITY_VERBOSE;
                break;

            default:
                $options = OutputInterface::VERBOSITY_NORMAL;
                $out = $this->output->getErrorOutput();
                break;
        }

        $out->writeLn($event->getMessage(), $options);

        return true;
    }
}
