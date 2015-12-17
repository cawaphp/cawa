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

use Cawa\Events\Event as EventBase;

class Event extends EventBase
{
    /**
     * @var Model
     */
    private $model;

    /**
     * @return Model
     */
    public function getModel() : Model
    {
        return $this->model;
    }

    /**
     * @param string $name
     * @param Model $model
     */
    public function __construct($name, Model $model)
    {
        $this->model = $model;
        parent::__construct($name);
    }
}
