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

class HtmlContainer extends HtmlElement
{
    use ContainerTrait {
        ContainerTrait::render as private containerRender;
    }

    /**
     * @return string
     */
    public function render()
    {
        if ($this->elements) {
            HtmlElement::setContent($this->containerRender());
        }

        $render = HtmlElement::render();

        return $render;
    }

    /**
     * @return array
     */
    public function renderOuter() : array
    {
        HtmlElement::setContent('[-INNER-]');

        $render = HtmlElement::render();

        return explode('[-INNER-]', $render);
    }
}
