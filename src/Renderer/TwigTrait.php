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

use Cawa\App\AbstractApp;
use Cawa\Controller\TemplateControllerTrait;
use Cawa\Controller\ViewDataTrait;
use Twig_Environment;
use Twig_Loader_Filesystem;

trait TwigTrait
{
    use ViewDataTrait;
    use TemplateControllerTrait;

    /**
     * @var \Twig_Environment
     */
    private static $renderer;

    /**
     * @return string
     */
    public function render()
    {
        if (!self::$renderer) {
            $loader = new Twig_Loader_Filesystem();
            $loader->prependPath('/');

            $twig = new Twig_Environment($loader, [
                'cache' => AbstractApp::getAppRoot() . '/cache/twig',
            ]);
            self::$renderer = $twig;
        }

        if (empty($this->templatePath)) {
            $this->setTemplatePath();
        }
        $template = self::$renderer->loadTemplate($this->templatePath . '.twig');

        return $template->render($this->getData());
    }
}
