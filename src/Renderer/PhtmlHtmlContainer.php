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

use DeepCopy\DeepCopy;

class PhtmlHtmlContainer extends HtmlContainer
{
    use PhtmlTrait {
        PhtmlTrait::render as private phtmlRender;
    }

    use ContainerTrait {
        ContainerTrait::render as private containerRender;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        /* @var \Cawa\Renderer\PhtmlHtmlContainer $clone */
        $deepcopy = new DeepCopy();
        $clone = $deepcopy->copy($this);

        $content = $clone->content ?: $clone->containerRender();
        $clone->addData('content', $content);
        $clone->content = $clone->phtmlRender();

        $clone->clear();

        return $clone->renderClone();
    }

    /**
     * @return string
     */
    private function renderClone()
    {
        return parent::render();
    }
}
