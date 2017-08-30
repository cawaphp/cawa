<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Cawa\Controller;

trait ViewDataTrait
{
    private $data = [];

    /**
     * @return array
     */
    protected function getViewData() : array
    {
        return $this->data;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return $this|self
     */
    protected function addViewData(string $name, $value)
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * @param array $datas
     *
     * @return $this|self
     */
    protected function addViewDatas(array $datas)
    {
        $this->data = array_replace_recursive($this->data, $datas);

        return $this;
    }

    /**
     * @param array $datas
     *
     * @return $this|self
     */
    protected function setViewDatas(array $datas)
    {
        $this->data = $datas;

        return $this;
    }
}
