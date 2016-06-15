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

namespace Cawa\Renderer;

class PhtmlHtmlElement extends HtmlElement
{
    use PhtmlTrait {
        PhtmlTrait::render as private phtmlRender;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $this->content = $this->phtmlRender();

        return parent::render();
    }
}
