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

class Element extends ViewController
{
    /**
     * HtmlElement constructor.
     *
     * @param string $content
     */
    public function __construct(string $content = null)
    {
        $this->content = $content;
    }

    /**
     * @var string
     */
    protected $content;

    /**
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return $this|self
     */
    public function setContent($content) : self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param string $content
     *
     * @return $this|self
     */
    public function prependContent(string $content) : self
    {
        $this->content = $content . $this->content;

        return $this;
    }

    /**
     * @param string $content
     *
     * @return $this|self
     */
    public function appendContent(string $content) : self
    {
        $this->content = $this->content . $content;

        return $this;
    }

    /**
     * @var bool
     */
    private $renderable = true;

    /**
     * @return bool
     */
    public function isRenderable() : bool
    {
        return $this->renderable;
    }

    /**
     * @param bool $renderable
     *
     * @return $this|self
     */
    public function setRenderable(bool $renderable) : self
    {
        $this->renderable = $renderable;

        return $this;
    }

    /**
     * @return string
     */
    public function render()
    {
        return $this->renderable ? $this->content : '';
    }
}
