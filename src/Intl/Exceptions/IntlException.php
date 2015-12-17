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

namespace Cawa\Intl\Exceptions;

use Cawa\Core\App;

class IntlException extends \Exception
{
    /**
     * @param string $message
     * @param \Exception $previous
     */
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return null|string
     */
    public function __toString()
    {
        return App::translator()->trans($this->message);
    }
}
