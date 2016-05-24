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

namespace Cawa\Orm;

class ModelSaved
{
    /**
     * @var Model
     */
    private $model;


    /**
     * @var array
     */
    private $changedProperties = [];

    /**
     * @var mixed
     */
    private $return;

    /**
     * @param Model $model
     * @param array $changedProperties
     * @param $return
     */
    public function __construct(Model $model, array $changedProperties, $return = null)
    {
        $this->model = $model;
        $this->changedProperties = $changedProperties;
        $this->return = $return;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return get_class($this->model);
    }

    /**
     * @return array
     */
    public function getChangedProperties() : array
    {
        return $this->changedProperties;
    }
}
