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

use Cawa\App\HttpApp;

abstract class AbstractController
{

    /**
     * @param string $name
     * @param array $data
     *
     * @return string
     */
    public function route(string $name, array $data = [])
    {
        return HttpApp::router()->getUri($name, $data);
    }
}
