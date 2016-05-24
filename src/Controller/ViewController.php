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

namespace Cawa\Controller;

use Cawa\Orm\ObjectTrait;

abstract class ViewController extends AbstractController
{
    use ObjectTrait;

    /**
     * @return string|array
     */
    abstract public function render();
}
