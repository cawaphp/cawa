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
     * @return $this|self
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
     * @return $this|self
     */
    public function addFirst(ViewController ...$elements)
    {
        foreach ($elements as $element) {
            array_unshift($this->elements, $element);
        }

        return $this;
    }

    /**
     * @return $this|self
     */
    public function clear()
    {
        $this->elements = [];

        return $this;
    }

    /**
     * @return ViewController
     */
    public function first()
    {
        return $this->elements[0] ?? null;
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return sizeof($this->elements);
    }

    /**
     * @param ViewController $compare
     *
     * @return int|null
     */
    protected function getIndex(ViewController $compare = null)
    {
        if (is_null($compare)) {
            return null;
        }

        $index = null;
        foreach ($this->elements as $i => $element) {
            if ($element === $compare) {
                $index = $i;
            }
        }

        return $index;
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
