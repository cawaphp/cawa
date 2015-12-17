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

use Cawa\Core\Controller\ViewController;

trait TraitContainer
{
    /**
     * @var ViewController[]
     */
    protected $elements = [];

    /**
     * @param ViewController $element
     *
     * @return $this
     */
    public function add(ViewController $element)
    {
        $this->elements[] = $element ;

        return $this;
    }

    /**
     * @param ViewController $element
     *
     * @return $this
     */
    public function addFirst(ViewController $element)
    {
        array_unshift($this->elements, $element);

        return $this;
    }

    /**
     * @return $this
     */
    public function clear() : self
    {
        $this->elements = [];

        return $this;
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return sizeof($this->elements);
    }

    /**
     * @return string
     */
    public function render()
    {
        $content = '';
        foreach ($this->elements as $element) {
            $content .= $element->render() . "\n";
        }

        return $content;
    }
}
