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

namespace Cawa\Controller;

/**
 * @mixin AbstractController
 */
trait TemplateControllerTrait
{
    /**
     * @var array
     */
    private $path;

    /**
     * @param string $context
     *
     * @return array
     */
    private function getPath(string $context = null)
    {
        if (!$this->path) {
            $reflection = new \ReflectionClass($context ?: $this);
            $filename = $reflection->getFileName();
            $this->path = pathinfo($filename);
        }

        return $this->path;
    }

    /**
     * @var string
     */
    protected $templatePath;

    /**
     * @param string $path if null revert to default one
     *
     * @param string $context
     *
     * @return $this|static
     */
    public function setTemplatePath(string $path = null, string $context = null) : self
    {
        if (empty($path)) {
            $this->templatePath = $this->getPath($context)['dirname'] . '/' . $this->getPath($context)['filename'];
        } elseif (substr($path, 0, 1) == '/') {
            $this->templatePath = $path;
        } else {
            $this->templatePath = $this->getPath($context)['dirname'] . '/' . $path;
        }

        return $this;
    }
}
