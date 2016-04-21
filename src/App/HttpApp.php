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

use Cawa\Error\Handler as ErrorHandler;
use Cawa\Router\RouterFactory;

class HttpApp extends AbstractApp
{
    use RouterFactory;
    use HttpFactory;

    /**
     * HttpApp constructor.
     *
     * @param string $appRoot
     */
    protected function __construct(string $appRoot)
    {
        parent::__construct($appRoot);

        ErrorHandler::register();

        ob_start();
    }

    /**
     * Load route & request
     */
    public function init()
    {
        parent::init();

        $this->request()->fillFromGlobals();

        if (file_exists($this->getAppRoot() . '/config/route.php')) {
            $this->router()->addRoutes(require $this->getAppRoot() . '/config/route.php');
        }

        if (file_exists($this->getAppRoot() . '/config/uri.php')) {
            $this->router()->addUris(require $this->getAppRoot() . '/config/uri.php');
        }
    }

    /**
     * @return void
     */
    public function handle()
    {
        $return = $this->router()->handle();

        // hack to display trace on development env
        $debug = (self::env() == self::DEV && ob_get_length() > 0);

        if ($return instanceof \SimpleXMLElement) {
            if ($debug == false) {
                $this->response()->addHeaderIfNotExist('Content-Type', 'text/xml; charset=utf-8');
            }

            $this->response()->setBody($return->asXML());
        }
        if (gettype($return) == 'array') {
            if ($debug == false) {
                $this->response()->addHeaderIfNotExist('Content-Type', 'application/json; charset=utf-8');
            }

            $this->response()->setBody(json_encode($return));
        } else {
            $this->response()->addHeaderIfNotExist('Content-Type', 'text/html; charset=utf-8');
            $this->response()->setBody($return);
        }
    }

    /**
     * @return void
     */
    public static function end()
    {
        parent::end();

        echo self::response()->send();

        $exitCode = self::response()->getStatus() >= 500 &&
            self::response()->getStatus() < 600 ? 1 : 0;

        exit($exitCode);
    }
}