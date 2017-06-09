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

namespace Cawa\Error;

use Cawa\Events\Event;

class ErrorEvent extends Event
{
    /**
     * @var \Throwable
     */
    private $exception;

    /**
     * @return \Throwable
     */
    public function getException() : \Throwable
    {
        return $this->exception;
    }

    public function __construct(\Throwable $exception)
    {
        parent::__construct('error.exception');

        $this->exception = $exception;
    }
}
