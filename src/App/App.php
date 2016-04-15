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

namespace Cawa\App;

use Cawa\Core\DI;
use Cawa\Error\Handler as ErrorHandler;
use Cawa\Events\DispatcherFactory;
use Cawa\Events\Event;
use Cawa\Http\ServerRequest;
use Cawa\Http\ServerResponse;
use Cawa\Log\Output\StdErr;
use Cawa\Router\Router;
use Psr\Log\LogLevel;

class App extends AbstractApp
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @return Router
     */
    public static function router() : Router
    {
        return self::$instance->router;
    }

    /**
     * @var ServerRequest
     */
    public $request;

    /**
     * @return ServerRequest
     */
    public static function request() : ServerRequest
    {
        return self::$instance->request;
    }

    /**
     * @var ServerResponse
     */
    public $response;

    /**
     * @return ServerResponse
     */
    public static function response() : ServerResponse
    {
        return self::$instance->response;
    }

    /**
     * App constructor.
     *
     * @param string $appRoot
     */
    protected function __construct(string $appRoot)
    {
        parent::__construct($appRoot);

        ErrorHandler::register();

        ob_start();

        $this->router = new Router();
    }

    /**
     * Load route & request
     */
    public function init()
    {
        parent::init();

        $this->request = ServerRequest::createFromGlobals();
        $this->response = new ServerResponse();

        if (file_exists($this->getAppRoot() . '/config/route.php')) {
            $this->router->addRoutes(require $this->getAppRoot() . '/config/route.php');
        }

        if (file_exists($this->getAppRoot() . '/config/uri.php')) {
            $this->router->addUris(require $this->getAppRoot() . '/config/uri.php');
        }

        $this->init = true;
    }

    /**
     * @return void
     */
    public function handle()
    {
        $return = $this->router->handle();

        // hack to display trace on development env
        $debug = (App::env() == App::DEV && ob_get_length() > 0);

        if ($return instanceof \SimpleXMLElement) {
            if ($debug == false) {
                $this->response->addHeaderIfNotExist('Content-Type', 'text/xml; charset=utf-8');
            }

            $this->response->setBody($return->asXML());
        }
        if (gettype($return) == 'array') {
            if ($debug == false) {
                $this->response->addHeaderIfNotExist('Content-Type', 'application/json; charset=utf-8');
            }

            $this->response->setBody(json_encode($return));
        } else {
            $this->response->addHeaderIfNotExist('Content-Type', 'text/html; charset=utf-8');
            $this->response->setBody($return);
        }
    }

    /**
     * @return void
     */
    public static function end()
    {
        parent::end();

        echo self::instance()->response()->send();

        $exitCode = self::instance()->response()->getStatus() >= 500 &&
            self::instance()->response()->getStatus() < 600 ? 1 : 0;

        exit($exitCode);
    }
}
