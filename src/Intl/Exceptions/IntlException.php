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

namespace Cawa\Intl\Exceptions;

use Cawa\Intl\TranslatorFactory;

class IntlException extends \Exception
{
    use TranslatorFactory;

    /**
     * @var array
     */
    private $data;

    /**
     * @param string $message
     * @param array $data
     * @param \Exception $previous
     */
    public function __construct($message, array $data = [], \Exception $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return null|string
     */
    public function __toString()
    {
        return self::trans($this->message, $this->data);
    }
}
