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

namespace Cawa\Controller;

/**
 * @mixin AbstractController
 */
trait TemplateControllerTrait
{

    /**
     * @var array
     */
    private static $path;

    /**
     *
     */
    private function getPath()
    {
        if (!self::$path) {
            $reflection = new \ReflectionClass($this);
            $filename = $reflection->getFileName();
            self::$path = pathinfo($filename);
        }

        return self::$path;
    }

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
            $this->templatePath = $this->getPath()['dirname'] . '/' . $this->getPath()['filename'];
        } elseif (substr($path, 0, 1) == '/') {
            $this->templatePath = $path;
        } else {
            $this->templatePath = $this->getPath()['dirname'] . '/' . $path;
        }

        return $this;
    }
}
