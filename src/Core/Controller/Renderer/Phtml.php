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

namespace Cawa\Core\Controller\Renderer;

use Cawa\Core\Controller\TemplateController;
use Cawa\Core\Controller\ViewData;

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
