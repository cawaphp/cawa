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

trait ContainerTrait
{
    /**
     * @var ViewController[]
     */
    protected $elements = [];

    /**
     * @param ViewController|ViewController[] ...$elements
     *
     * @return $this
     */
    public function add(ViewController ...$elements)
    {
        foreach ($elements as $element) {
            $this->elements[] = $element;
        }

        return $this;
    }

    /**
     * @param ViewController|ViewController[] ...$elements
     *
     * @return $this
     */
    public function addFirst(ViewController ...$elements)
    {
        foreach ($elements as $element) {
            array_unshift($this->elements, $element);
        }

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
     * @return ViewController[]
     */
    public function getElements() : array
    {
        return $this->elements;
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
