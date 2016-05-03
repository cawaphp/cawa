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

class WidgetOption extends ViewController
{
    /**
     * @var HtmlElement
     */
    private $element;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->element = new HtmlElement('<script>');
        $this->element->addAttribute('type', 'application/json');
        $this->data = $data;
    }

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return $this
     */
    public function addData(string $key, $value) : self
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data) : self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string
     */
    public function render()
    {
        $this->element->setContent(json_encode($this->data));

        return $this->element->render();
    }
}
