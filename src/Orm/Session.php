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

use Cawa\Core\App;

trait Session
{
    /**
     * @param string $name
     *
     * @return void
     */
    public function sessionSave(string $name = null)
    {
        if (!$name) {
            $name = get_class();
        }

        $data = $this;
        if (in_array(SessionSleep::class, class_uses($this))) {
            $data = $this->sessionSleep();
        }

        App::session()->set($name, $data);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function sessionExists(string $name = null) : bool
    {
        if (!$name) {
            $name = get_class();
        }

        return App::session()->exist($name);
    }

    /**
     * @param string $name
     *
     * @return null|object
     */
    public static function sessionReload(string $name = null)
    {
        if (!$name) {
            $name = get_class();
        }

        $data = App::session()->get($name);

        if (!$data) {
            return false;
        }

        $class = get_called_class();

        if (method_exists($class, 'sessionWakeup')) {
            $data = $class::sessionWakeup($data);
        }

        return $data;
    }
}
