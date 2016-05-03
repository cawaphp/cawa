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

class WidgetElement extends ViewController
{
    /**
     * @var ViewController|ContainerTrait
     */
    private $element;

    /**
     * @return ViewController|ContainerTrait
     */
    public function getElement() : ViewController
    {
        return $this->element;
    }

    /**
     * @var WidgetOption
     */
    private $options;

    /**
     * @return WidgetOption
     */
    public function getOptions() : WidgetOption
    {
        return $this->options;
    }

    /**
     * @param ViewController $element
     * @param array $data
     */
    public function __construct(ViewController $element, array $data = [])
    {
        $this->element = $element;
        $this->options = new WidgetOption($data);
    }

    /**
     * @return string
     */
    public function render()
    {
        return $this->element->render() . $this->options->render();
    }
}
