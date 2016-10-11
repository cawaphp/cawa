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

trait ViewDataTrait
{
    private $data = [];

    /**
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return $this|self
     */
    public function addData(string $name, $value)
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * @param array $datas
     *
     * @return $this|self
     */
    public function addDatas(array $datas)
    {
        $this->data = array_merge($this->data, $datas);

        return $this;
    }
}
