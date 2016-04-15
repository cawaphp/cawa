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

use Cawa\Controller\ViewController;

class PhtmlContainer extends ViewController
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
        $this->addData('content', $this->containerRender());

        return $this->phtmlRender();
    }
}
