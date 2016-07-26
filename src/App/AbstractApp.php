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

namespace Cawa\App;

use Cawa\Core\DI;
use Cawa\Events\DispatcherFactory;
use Cawa\Events\Event;
use Cawa\Http\ServerRequest;
use Cawa\Log\Output\StdErr;
use Psr\Log\LogLevel;

abstract class AbstractApp
{
    use DispatcherFactory;

    /**
     * @var bool
     */
    protected $init = false;

    /**
     * @return bool
     */
    public static function isInit() : bool
    {
        return self::$instance && self::$instance->init;
    }

    /**
     * @var static
     */
    protected static $instance;

    /**
     * @return $this
     */
    public static function instance()
    {
        if (!self::$instance) {
            throw new \LogicException('HttpApp is not created');
        }

        return self::$instance;
    }

    /**
     * @param string $appRoot
     * @param ServerRequest|null $request
     *
     * @return $this
     */
    public static function create(string $appRoot, ServerRequest $request = null) : self
    {
        if (self::$instance) {
            throw new \LogicException('HttpApp is already created');
        }

        self::$instance = new static($appRoot, $request);

        return self::$instance;
    }

    /**
     * @var string
     */
    private $appRoot;

    /**
     * @return string
     */
    public static function getAppRoot() : string
    {
        return self::$instance->appRoot;
    }

    /**
     * Environnement development
     */
    const DEV = 'development';

    /**
     * Environnement production
     */
    const PROD = 'production';

    /**
     * Environnement testing
     */
    const TEST = 'testing';

    /**
     * @var string
     */
    private $env = 'production';

    /**
     * @return string
     */
    public static function env() : string
    {
        return self::$instance->env;
    }

    /**
     * HttpApp constructor.
     *
     * @param string $appRoot
     */
    protected function __construct(string $appRoot)
    {
        self::$instance = $this;

        $this->appRoot = $appRoot;
        $this->env = getenv('APP_ENV') ? getenv('APP_ENV') : self::DEV;
    }

    /**
     * Load config
     */
    public function init()
    {
        if ($this->init == true) {
            throw new \LogicException("Can't reinit App");
        }

        if (file_exists($this->appRoot . '/config/config.php')) {
            DI::config()->add(require $this->appRoot . '/config/config.php');
        }

        date_default_timezone_set(DI::config()->get('timezone'));

        $this->addLoggerListeners();
        $this->addConfigListeners();

        $this->init = true;
    }

    /**
     * @return bool
     */
    private function addLoggerListeners() : bool
    {
        $loggers = DI::config()->getIfExists('logger');

        // StdErr default logger
        $logger = new StdErr();
        $logger->setMinimumLevel(LogLevel::WARNING);
        $loggers[] = $logger;

        if (!is_array($loggers)) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid logger configuration, expected array got '%s'",
                gettype($loggers)
            ));
        }

        foreach ($loggers as $logger) {
            self::dispatcher()->addListenerByClass('Cawa\\Log\\Event', [$logger, 'receive']);
        }

        return !$loggers;
    }

    /**
     *
     */
    private function addConfigListeners()
    {
        foreach (DI::config()->getIfExists('listeners/byClass') ?? [] as $class => $listeners) {
            foreach ($listeners as $listener) {
                self::dispatcher()->addListenerByClass($class, $listener);
            }
        }

        foreach (DI::config()->getIfExists('listeners/byName') ?? [] as $name => $listeners) {
            foreach ($listeners as $listener) {
                self::dispatcher()->addListener($name, $listener);
            }
        }
    }

    /**
     * @var Module[]
     */
    private $modules = [];

    /**
     * @param Module $module
     */
    public function registerModule(Module $module)
    {
        if ($module->init()) {
            $this->modules[] = $module;
        }
    }

    /**
     * @param string $class
     *
     * @throws \InvalidArgumentException
     *
     * @return Module
     */
    public function getModule(string $class) : Module
    {
        foreach ($this->modules as $module) {
            if ($module instanceof $class) {
                return $module;
            }
        }

        throw new \InvalidArgumentException("No register module modules with class '%s'", get_class($class));
    }

    /**
     * @return void
     */
    abstract public function handle();

    /**
     * @return void
     */
    public function end()
    {
        self::dispatcher()->emit(new Event('app.end'));
    }
}
