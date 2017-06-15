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

namespace Cawa\App;

use Cawa\Core\DI;
use Cawa\Error\Handler as ErrorHandler;
use Cawa\Router\RouterFactory;

class HttpApp extends AbstractApp
{
    use RouterFactory;
    use HttpFactory;

    /**
     *  Token cookie name.
     */
    const COOKIE_ADMIN = 'ADM';

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
     * Load route & request.
     */
    public function init()
    {
        parent::init();

        self::request()->fillFromGlobals();
    }

    /**
     * @return bool
     */
    public function isAdmin() : bool
    {
        $cookie = DI::config()->getIfExists('admin/cookie');

        if ($cookie &&
            self::request()->getCookie(self::COOKIE_ADMIN) &&
            self::request()->getCookie(self::COOKIE_ADMIN)->getValue() === $cookie
        ) {
            return true;
        }

        return parent::isAdmin();
    }

    /**
     *
     */
    public function handle()
    {
        $return = self::router()->handle();

        // handle error status
        if (!$return && self::router()->hasError(self::response()->getStatus())) {
            $return = self::router()->returnError(self::response()->getStatus());
        }

        // hack to display trace on development env
        $debug = (self::env() != self::PRODUCTION && ob_get_length() > 0);

        if ($return instanceof \SimpleXMLElement) {
            if ($debug == false) {
                self::response()->addHeaderIfNotExist('Content-Type', 'text/xml; charset=utf-8');
            }

            self::response()->setBody($return->asXML());
        }
        if (gettype($return) == 'array') {
            if ($debug == false) {
                self::response()->addHeaderIfNotExist('Content-Type', 'application/json; charset=utf-8');
            }

            self::response()->setBody(json_encode($return));
        } else {
            self::response()->addHeaderIfNotExist('Content-Type', 'text/html; charset=utf-8');
            self::response()->setBody($return);
        }
    }

    /**
     */
    public function end()
    {
        parent::end();

        echo self::response()->send();

        $exitCode = self::response()->getStatus() >= 500 &&
            self::response()->getStatus() < 600 ? 1 : 0;

        exit($exitCode);
    }
}
