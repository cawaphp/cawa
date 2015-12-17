<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Cawa\Core\Controller;

/**
 * @mixin AbstractController
 */
trait TemplateController
{
    /**
     * @var string
     */
    protected $templatePath;

    /**
     * @param string $path if null revert to default one
     *
     * @return $this
     */
    public function setTemplatePath(string $path = null) : self
    {
        if (empty($path)) {
            $this->templatePath = $this->path['dirname'] . '/' . $this->path['filename'];
        } elseif (substr($path, 0, 1) == '/') {
            $this->templatePath = $path;
        } else {
            $this->templatePath = $this->path['dirname'] . '/' . $path;
        }

        // $this->event->addData(array('template' => $this->templatePath));

        return $this;
    }
}
