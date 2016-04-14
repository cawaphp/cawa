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

namespace Cawa\Session;

use Cawa\App\App;
use Cawa\Core\DI;
use Cawa\Events\DispatcherFactory;
use Cawa\Events\TimerEvent;
use Cawa\Http\Cookie;
use Cawa\Session\SessionStorage\AbstractStorage;

class Session
{
    use DispatcherFactory;

    /**
     * @var AbstractStorage
     */
    private $storage;

    /**
     * Session constructor.
     */
    public function __construct()
    {
        // name
        $name = DI::config()->getIfExists('session/name') ?? 'SID';
        $this->setName($name);

        // storage
        $class = DI::config()->getIfExists('session/storage/class') ?? '\\Cawa\\Session\\SessionStorage\\FileStorage';
        $args = DI::config()->getIfExists('session/storage/arguments') ?? [];

        $this->storage = new $class;
        call_user_func_array([$this->storage, '__construct'], $args);

        // disable internal session handler
        $callable = function () {
            // this exception will not be displayed, instead "FatalErrorException"
            // with "Error: session_start(): Failed to initialize storage module: user (path: /var/lib/php/sessions)"
            throw new \LogicException('Cannot used internal session in Сáша App');
        };
        session_set_save_handler($callable, $callable, $callable, $callable, $callable, $callable, $callable);
    }

    public function init()
    {
        if (!self::$init) {
            $event = new TimerEvent('session.open');
            $this->storage->open();
            self::dispatcher()->emit($event);

            if (App::request()->getCookie($this->name)) {
                $this->id = App::request()->getCookie($this->name)->getValue();
            }

            if (!$this->id) {
                $this->create();
            } else {
                $event = new TimerEvent('session.read');
                $readData = $this->storage->read($this->id);

                $maxDuration = DI::config()->getIfExists('session/maxDuration') ?? ini_get('session.gc_maxlifetime');

                if ($readData === false) {
                    $this->create();
                } else {
                    list($this->data, $this->startTime, $this->accessTime, $length) = $readData;
                }

                if (isset($length)) {
                    $event->setData(['length' => $length]);
                }
                self::dispatcher()->emit($event);

                if ($maxDuration > $this->accessTime + $maxDuration) {
                    $this->create();
                }
            }

            $this->addHeaders();

            self::dispatcher()->addListener("app.end", function()
            {
                $this->save();
            });

            self::$init = true;
        }
    }

    /**
     * @return void
     */
    private function create()
    {
        $this->id = md5(uniqid((string) rand(), true));
        App::response()->addCookie(new Cookie($this->name, $this->id));
    }

    /**
     * add expiration headers to avoid caching
     */
    private function addHeaders()
    {
        App::response()->addHeader('Cache-Control', 'no-cache, no-store, must-revalidate'); // HTTP 1.1
        App::response()->addHeader('Pragma', 'no-cache'); // HTTP 1.0
        App::response()->addHeader('Expires', '-1'); // Proxies
    }

    /**
     * is the session is init and data loaded
     *
     * @var bool
     */
    protected static $init = false;

    /**
     * @return bool
     */
    public function isStarted() : bool
    {
        return self::$init;
    }

    /**
     * is the data have changed
     *
     * @var bool
     */
    private $changed = false;

    /**
     * The session id
     *
     * @var string
     */
    private $id;

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * The name of the session cookie
     *
     * @var string
     */
    private $name = 'sid';

    /**
     * Gets the name of the cookie.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name) : self
    {
        // from PHP source code
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        if (empty($name)) {
            throw new \InvalidArgumentException('The cookie name cannot be empty.');
        };

        $this->name = $name;

        return $this;
    }

    /**
     * @var int
     */
    protected $startTime;

    /**
     * @return int
     */
    public function getStartTime() : int
    {
        return $this->startTime;
    }

    /**
     * @var int
     */
    protected $accessTime;

    /**
     * @return int
     */
    public function getAccessTime() : int
    {
        return $this->accessTime;
    }

    /**
     * @var array
     */
    private $data = [];

    /**
     * @return array
     */
    public function getData() : array
    {
        if (self::$init == false) {
            $this->init();
        }

        return $this->data;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function get(string $name)
    {
        if (self::$init == false) {
            $this->init();
        }

        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * @param string $name
     * @param $value
     *
     * @return $this
     */
    public function set(string $name, $value) : self
    {
        if (self::$init == false) {
            $this->init();
        }

        if (isset($this->data[$name]) && $this->data[$name] == $value) {
            return $this;
        }

        $this->changed = true;

        $this->data[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function remove(string $name) : self
    {
        if (self::$init == false) {
            $this->init();
        }

        $this->changed = true;

        unset($this->data[$name]);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function exist(string $name) : bool
    {
        if (self::$init == false) {
            $this->init();
        }

        return isset($this->data[$name]) ? true  : false;
    }

    /**
     * @return bool
     */
    public function save() : bool
    {
        if (!self::$init) {
            return true;
        }

        $this->accessTime = time();
        if (!$this->startTime) {
            $this->startTime = $this->accessTime;
        }

        $ttl = DI::config()->getIfExists('session/refreshTtl') ?? 60;
        if (!$this->changed && $this->accessTime + $ttl < time()) {
            $event = new TimerEvent('session.touch');
            $return = $this->storage->touch($this->id, $this->data, $this->startTime, $this->accessTime);
        } else {
            $event = new TimerEvent('session.write');
            $return = $this->storage->write($this->id, $this->data, $this->startTime, $this->accessTime);
        }

        $event->setData(['length' => $return]);
        self::dispatcher()->emit($event);

        return $return === false ? false : true;
    }
}
