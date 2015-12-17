<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Cawa\Events;

class Dispatcher
{
    /**
     * @var array
     */
    protected $listeners = [];

    /**
     * @var array
     */
    protected $classListeners = [];

    /**
     * Listen to a specific event name
     *
     * @param string $event
     * @param callable $listener
     */
    public function addListener(string $event, $listener)
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }
        $this->listeners[$event][] = $listener;
    }

    /**
     * Listen all event for the specific class (or all of this parent class)
     *
     * @param string $class
     * @param callable $listener
     */
    public function addListenerByClass(string $class, $listener)
    {
        $class = trim($class, '\\');

        if (!isset($this->classListeners[$class])) {
            $this->classListeners[$class] = [];
        }
        $this->classListeners[$class][] = $listener;
    }

    /**
     * @param string $event
     * @param callable $listener
     */
    public function once(string $event, $listener)
    {
        $onceListener = function () use (&$onceListener, $event, $listener) {
            $this->removeListener($event, $onceListener);
            call_user_func_array($listener, func_get_args());
        };

        $this->addListener($event, $onceListener);
    }

    /**
     * @param string $event
     * @param callable $listener
     */
    public function removeListener(string $event, $listener)
    {
        if (isset($this->listeners[$event])) {
            $index = array_search($listener, $this->listeners[$event], true);
            if (false !== $index) {
                unset($this->listeners[$event][$index]);
            }

            $index = array_search($listener, $this->classListeners[$event], true);
            if (false !== $index) {
                unset($this->classListeners[$event][$index]);
            }
        }
    }

    /**
     * @param string $event
     */
    public function removeAllListeners(string $event = null)
    {
        if ($event !== null) {
            unset($this->listeners[$event]);
            unset($this->classListeners[$event]);
        } else {
            $this->listeners = [];
            $this->classListeners = [];
        }
    }

    /**
     * @param string $event
     *
     * @return array
     */
    public function getListeners(string $event)
    {
        if (isset($this->listeners[$event])) {
            return $this->listeners[$event];
        } elseif (isset($this->classListeners[$event])) {
            return $this->classListeners[$event];
        }

        return [];
    }

    /**
     * @param Event[] $events
     */
    public function emits(array $events)
    {
        foreach ($events as $event) {
            $this->emit($event);
        }
    }

    /**
     * @param Event $event
     */
    public function emit(Event $event)
    {
        $event->onEmit();

        if ($this->listeners) {
            if (isset($this->listeners[$event->getName()])) {
                foreach ($this->listeners[$event->getName()] as $listener) {
                    call_user_func($listener, $event);
                }
            }
        }

        // call class listeners
        if ($this->classListeners) {
            foreach ($this->getParentClasses(get_class($event)) as $class) {
                if (isset($this->classListeners[$class])) {
                    foreach ($this->classListeners[$class] as $listener) {
                        call_user_func($listener, $event);
                    }
                }
            }
        }
    }

    /**
     * @param string $class
     * @param array $parentClass
     *
     * @return array
     */
    private function getParentClasses(string $class, $parentClass = []) : array
    {
        if (!$parentClass) {
            $parentClass[] = $class;
        }

        $parent = get_parent_class($class);
        if ($parent) {
            $parentClass[] = $parent;
            $parentClass = self::getParentClasses($parent, $parentClass);
        }

        return $parentClass;
    }
}
