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

namespace Cawa\App\Controller\Renderer;

class EmptyElement extends HtmlElement
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('<empty />');
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return '';
    }
}
