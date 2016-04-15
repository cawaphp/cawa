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

namespace Cawa\App\Controller\Renderer;

use Cawa\App\Controller\ViewController;
use ReflectionClass;

class Element extends ViewController
{
    /**
     * HtmlElement constructor.
     *
     * @param string $tag
     * @param string $content
     */
    public function __construct(string $content = null)
    {
        $this->content = $content;

        parent::__construct();
    }

    /**
     * @param array ...$args
     *
     * @return static
     */
    public static function create(... $args) : self
    {
        $class = static::class;

        return (new ReflectionClass($class))->newInstanceArgs($args);
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
     * @return $this
     */
    public function setContent(string $content) : self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @throws \LogicException
     *
     * @return string
     */
    public function render()
    {
        return $this->content;
    }
}
