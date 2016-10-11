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

use Cawa\Controller\TemplateControllerTrait;
use Cawa\Controller\ViewDataTrait;

/**
 * @mixin ViewDataTrait
 */
trait PhtmlTrait
{
    use TemplateControllerTrait;

    /**
     * @return string
     */
    public function render()
    {
        if (empty($this->templatePath)) {
            $this->setTemplatePath();
        }

        $closure = \Closure::bind(function(string $path, array $data) {
            extract($data);
            ob_start();
            /* @noinspection PhpIncludeInspection */
            require $path . '.phtml';

            return ob_get_clean();
        }, $this, get_class($this));

        return $closure($this->templatePath, $this->getData());

        /*
        $data = $this->getData();

        extract($data);
        ob_start();
        require $this->templatePath . '.phtml';
        $render = ob_get_clean();

        return $render;
        */
    }

    /**
     * Escape content
     *
     * @param string $content
     *
     * @return string
     */
    public function escape(string $content) : string
    {
        return htmlspecialchars($content);
    }
}
