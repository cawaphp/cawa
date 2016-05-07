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
use Cawa\Controller\ViewController;
use Cawa\Controller\ViewDataTrait;

class PhtmlTemplate extends ViewController
{
    use PhtmlTrait;
    use ViewDataTrait;
    use AssetTrait;

    /**
     * @param string $templatePath
     */
    private function __construct(string $templatePath)
    {
        $this->setTemplatePath($templatePath);
    }

    /**
     * @param string $templatePath
     * @param array $data
     *
     * @return string
     */
    public static function renderTemplate(string $templatePath, array $data = []) : string
    {
        if (substr($templatePath, 0, 1) != '/') {
            $templatePath = AbstractApp::getAppRoot() . '/' . $templatePath;
        }

        $phtml = new static($templatePath);
        $phtml->addDatas($data);

        return $phtml->render();
    }
}
