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

use Cawa\Controller\TemplateController;
use Cawa\Controller\ViewData;

/**
 * @mixin ViewData
 */
trait Phtml
{
    use TemplateController;

    /**
     * @return string
     */
    public function render()
    {
        if (empty($this->templatePath)) {
            $this->setTemplatePath();
        }

        $data = $this->getData();

        extract($data);
        ob_start();
        require $this->templatePath . '.phtml';
        $render = ob_get_clean();

        return $render;
    }
}
