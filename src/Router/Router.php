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

namespace Cawa\Router;

use Behat\Transliterator\Transliterator;
use Cawa\App\HttpFactory;
use Cawa\Cache\CacheFactory;
use Cawa\Controller\AbstractController;
use Cawa\Date\DateTime;
use Cawa\Events\DispatcherFactory;
use Cawa\Events\TimerEvent;
use Cawa\Intl\TranslatorFactory;
use Cawa\Log\LoggerFactory;
use Cawa\Net\Uri;
use Cawa\Session\SessionFactory;

class Router
{
    use LoggerFactory;
    use DispatcherFactory;
    use TranslatorFactory;
    use CacheFactory;
    use SessionFactory;
    use HttpFactory;

    const OPTIONS_SESSION = 'SESSION';
    const OPTIONS_CACHE = 'CACHE';
    const OPTIONS_MASTERPAGE = 'MASTERPAGE';
    const OPTIONS_CONDITIONS = 'CONDITIONS';

    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * @var Route[]
     */
    private $errors = [];

    /**
     * @var Route
     */
    private $currentRoute;

    /**
     * @return Route|null
     */
    public function current()
    {
        return $this->currentRoute;
    }

    /**
     * @var array
     */
    private $args;

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getArg(string $name)
    {
        if (!is_array($this->args)) {
            throw new \LogicException(sprintf("Ask for arg '%s' before the router init is finished", $name));
        }

        return $this->args[$name] ?? null;
    }

    /**
     * @param array $routes
     *
     * @throws \InvalidArgumentException
     */
    public function addRoutes(array $routes)
    {
        /** @var Route[] $routesTranform */
        $routesTranform = [];

        foreach ($routes as $name => $route) {
            if (is_string($route)) {
                $routesTranform[$name] = new Route($route);
            } elseif ($route instanceof Group) {
                $routesTranform = array_merge($routesTranform, $this->addGroup($route));
            } else {
                $routesTranform[$name] = $route;
            }
        }

        foreach ($routesTranform as $name => $route) {
            if (!$route->getName() && !$route->getResponseCode()) {
                $route->setName($name);
            }

            if (!$route instanceof Route) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid route, got %s',
                    is_object($route) ? get_class($route) : gettype($route)
                ));
            }

            if (!$route->getName() && !$route->getResponseCode()) {
                throw new \InvalidArgumentException('Missing route name');
            }

            if (isset($this->routes[$route->getName()])) {
                throw new \InvalidArgumentException(sprintf("Duplicate route name '%s'", $route->getName()));
            }

            if ($route->getResponseCode()) {
                $this->errors[$route->getResponseCode()] = $route;
            } else {
                $this->routes[$route->getName()] = $route;
            }
        }
    }

    /**
     * @param Group $group
     * @param array $names
     * @param array $match
     *
     * @return array
     */
    private function addGroup(Group $group, array $names = [], array $match = []) : array
    {
        $return = [];
        if ($group->getName()) {
            $names[] = $group->getName();
        }
        if ($group->getMatch()) {
            $match[] = $group->getMatch();
        }

        /** @var Route|Group|string $route */
        foreach ($group->getRoutes() as $name => $route) {
            $routeName = $names;
            $routeMatch = $match;

            if (is_string($route) || $route instanceof Route) {
                if (is_string($route)) {
                    $route = (new Route($route))
                        ->setName($name);
                } elseif ($name) {
                    $route->setName($name);
                }

                if ($route->getMatch()) {
                    $routeMatch[] = $route->getMatch();
                }

                if ($route->getName()) {
                    $routeName[] = $route->getName();
                }

                $route
                    ->setName(implode('/', $routeName))
                    ->setMatch('/' . implode('/', $routeMatch))
                ;

                $route->addGroupConfiguration($group);

                $return[$route->getName()] = $route;
            } elseif ($route instanceof Group) {
                $route->addGroupConfiguration($group);

                $return = array_merge(
                    $return,
                    $this->addGroup($route, $names, $match)
                );
            } else {
                throw new \InvalidArgumentException(sprintf("Invalid route type '%s'", gettype($route)));
            }
        }

        return $return;
    }

    /**
     * @var array
     */
    private $uris = [];

    /**
     * @param array $uris
     */
    public function addUris(array $uris)
    {
        foreach ($uris as $key => &$uri) {
            foreach ($uri as $locale => &$value) {
                $value = Transliterator::urlize($value);
            }
        }

        $this->uris = array_merge_recursive($this->uris, $uris);
    }

    /**
     * @param string $name
     * @param array $data
     * @param bool $warnData
     *
     * @return Uri
     */
    public function getUri(string $name, array $data = [], $warnData = true) : Uri
    {
        if (!isset($this->routes[$name])) {
            throw new \InvalidArgumentException(sprintf("Invalid route name '%s'", $name));
        }

        $route = $this->routes[$name];

        $uri = new Uri();
        $uri->removeAllQueries()
            ->setFragment(null)
            ->setPath($this->routeRegexp($route, $data))
        ;

        // $cloneData = $data;

        // append querystring
        if ($route->getUserInputs()) {
            $queryToAdd = [];
            foreach ($route->getUserInputs() as $querystring) {
                if (!isset($data[$querystring->getName()]) &&
                    $querystring->isMandatory() &&
                    $warnData &&
                    $route->getMethod() != 'POST' &&
                    $route->getMethod() != 'PUT' &&
                    $route->getMethod() != 'DELETE'
                ) {
                    throw new \InvalidArgumentException(sprintf(
                        "Missing querystring '%s' to generate route '%s'",
                        $querystring->getName(),
                        $route->getName()
                    ));
                }

                if (isset($data[$querystring->getName()])) {
                    // unset($cloneData[$querystring->getName()]);
                    $queryToAdd[$querystring->getName()] = $data[$querystring->getName()];
                }
            }

            $uri->addQueries($queryToAdd);
        }

        /*
        // Args can be consumed by routeRegexp and not remove from cloneData
        if (sizeof($cloneData)) {
            throw new \InvalidArgumentException(sprintf(
                "Too many data to generate route '%s' with keys %s",
                $route->getName(),
                "'" . implode("', '", array_keys($cloneData)) . "'"
            ));
        }
        */

        return $uri;
    }

    /**
     * @param string $url
     * @param Route $route
     *
     * @return array
     */
    private function match(string $url, Route $route) : array
    {
        $regexp = $this->routeRegexp($route);

        if (preg_match_all('`^' . $regexp . '$`', $url, $matches, PREG_SET_ORDER)) {
            // remove all numeric matches
            $matches = array_diff_key($matches[0], range(0, count($matches[0])));

            // control query string
            if ($route->getUserInputs()) {
                foreach ($route->getUserInputs() as $querystring) {
                    $method = self::request()->getMethod() == 'POST' ? 'getPostOrQuery' : 'getQuery';
                    $value = self::request()->$method($querystring->getName(), $querystring->getType());

                    if (is_null($value) && $querystring->isMandatory()) {
                        return [false, null, $regexp];
                    }

                    if (!is_null($value)) {
                        $matches[$querystring->getName()] = $value;
                    }
                }
            }

            // control conditions
            if ($route->getConditions()) {
                foreach ($route->getConditions() as $condition) {
                    if (!$condition($matches)) {
                        return [false, null, $regexp];
                    }
                }
            }

            return [true, $matches, $regexp];
        }

        return [false, null, $regexp];
    }

    /**
     * @param Route $route
     * @param array $data
     *
     * @return string
     */
    private function routeRegexp(Route $route, array $data = null) : string
    {
        $regexp = $route->getMatch();

        // AbstractApp::logger()->debug($regexp);

        preg_match_all('`\{\{(.+)\}\}`U', $regexp, $matches);
        if (sizeof($matches[1]) == 0) {
            return $regexp;
        }

        foreach ($matches[1] as $var) {
            $replace = '{{' . $var . '}}';
            $explode = explode(':', $var);
            $type = array_shift($explode);
            $value = implode(':', $explode) ;
            unset($dest);

            switch ($type) {
                case 'T':
                    $dest = $this->routeRegexpTranslatedText($route, $value, $data);
                    break;

                case 'L':
                    $dest = $this->routeRegexpLocales($data);
                    break;

                case 'C': // mandatory capture variable with optionnal regexp
                case 'O': // optionnal capture variable with optionnal regexp
                case 'S': // uncapture text with optionnal regexp
                    $dest = $this->routeRegexpCapture($type, $route, $value, $data);
                    break;

                default:
                    throw new \InvalidArgumentException(
                        sprintf("Invalid route variable '%s' on route '%s'", $type, $route->getName())
                    );
            }

            $regexp = str_replace(
                $replace,
                isset($dest) ? $dest : '',
                $regexp
            );
        }

        return $regexp;
    }

    /**
     * Translate text => accueil|home
     *
     * @param Route $route
     * @param string|null $value
     * @param array|null $data
     *
     * @return string
     */
    private function routeRegexpTranslatedText(Route $route, string $value = null, array $data = null) : string
    {
        if (!$value) {
            throw new \InvalidArgumentException(
                sprintf("Missing router var on route '%s'", $route->getName())
            );
        }

        $variable = null;
        $trans = [];

        if (strpos($value, '<') !== false && strpos($value, '>')) {
            $variable = substr($value, 1, strpos($value, '>') - 1);
            $keys = explode('|', substr($value, strpos($value, '>') + 1));
        } else {
            $keys = [$value];
        }

        foreach ($keys as $key) {
            if (!isset($this->uris[$key])) {
                throw new \InvalidArgumentException(
                    sprintf("Missing translations for var '%s' on route '%s'", $key, $route->getName())
                );
            }

            foreach ($this->uris[$key] as $locale => $current) {
                $trans[$locale][$key] = $current;
                $trans['*'][] = $current;
            }
        }

        if (!is_null($data) && sizeof($keys) > 1 && !isset($data[$variable])) {
            throw new \InvalidArgumentException(
                sprintf("Missing datas '%s' on route '%s'", $value, $route->getName())
            );
        }

        if (!is_null($data) && sizeof($keys) == 1) {
            $dest = $trans[self::locale()][$value];
        } elseif (!is_null($data) && sizeof($keys) > 1) {
            $dest = $data[$variable];
        } else {
            $dest = ($variable ? '(?<' . $variable . '>' : '(?:') . implode('|', $trans['*']) . ')';
        }

        return $dest;
    }

    /**
     * Locales avaialble => fr|en
     *
     * @param array|null $data
     *
     * @return string
     */
    private function routeRegexpLocales(array $data = null) : string
    {
        if (!is_null($data)) {
            $dest = self::locale();
        } else {
            $dest = '(?:' . implode('|', self::translator()->getLocales()) . ')';
        }

        return $dest;
    }

    /**
     * @param string $type
     * @param Route $route
     * @param string|null $value
     * @param array|null $data
     *
     * @return string
     */
    private function routeRegexpCapture(string $type, Route $route, string $value = null, array $data = null) : string
    {
        $begin = substr($value, 0, strpos($value, '<'));
        $variable = substr($value, strpos($value, '<') + 1, strpos($value, '>') - strpos($value, '<') - 1);
        $capture = substr($value, strpos($value, '>') + 1);

        if (empty($capture)) {
            $capture = '[^/]+';
        }

        if (!is_null($data)) {
            if (!array_key_exists($variable, $data) && ($type == 'C' || $type == 'S')) {
                throw new \InvalidArgumentException(sprintf(
                    "Missing variable route '%s' to generate route '%s'",
                    $variable,
                    $route->getName()
                ));
            }

            if (isset($data[$variable])) {
                if ($route->getOption(AbstractRoute::OPTIONS_URLIZE) === false) {
                    $dest = $data[$variable];
                } else {
                    $dest = Transliterator::urlize($data[$variable]);
                }
            }
        } else {
            $dest = '(';
            if ($begin) {
                $dest .= '(?:' . $begin . ')';
            }

            if ($type == 'S') {
                $dest .= '(?:' . $capture . ')';
            } else {
                $dest .= '(?<' . $variable . '>' . $capture . ')';
            }

            $dest .= ')';

            if ($type == 'O') {
                $dest .= '?';
            }
        }

        return $dest;
    }

    /**
     * @return string|array|\SimpleXMLElement|null
     */
    public function handle()
    {
        $uri = clone self::request()->getUri();
        $uri->removeAllQueries();
        $url = $uri->get();

        $event = new TimerEvent('router.match');

        $count = 0;
        foreach ($this->routes as $route) {
            list($result, $args, $regexp) = $this->match($url, $route);

            if ($route->getMethod() && $route->getMethod() != self::request()->getMethod()) {
                $result = false;
            }

            $count++;

            if ($result == false) {
                continue;
            }

            $event->setData([
                'analyseRoute' => $count,
                'name' => $route->getName(),
                'regexp' => $regexp,
                'controller' => is_string($route->getController()) ? $route->getController() : 'Callable',
                'args' => $args,
            ]);

            self::emit($event);

            $cacheKey = $this->cacheKey($route, $args);

            if ($return = $this->cacheGet($route, $cacheKey)) {
                return $return;
            }

            $return = $this->callController($route, $args);

            $this->cacheSet($route, $cacheKey, $return);

            return $return;
        }

        return $this->returnError(self::response()->getStatus() == 200 ? 404 : self::response()->getStatus());
    }

    /**
     * @param int $code
     *
     * @return mixed
     */
    public function returnError(int $code)
    {
        if (isset($this->errors[$code])) {
            self::response()->setStatus($code);

            return $this->callController($this->errors[$code], []);
        } else {
            return '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">' . "\n" .
                '<html><head>' . "\n" .
                '<title>' . self::response()->getStatusString($code) . '</title>' . "\n" .
                '</head><body>' . "\n" .
                '<h1>' . self::response()->getStatusString($code) . '</h1>' . "\n" .
                '</body></html>';
        }
    }

    /**
     * @param Route $route
     * @param array $args
     *
     * @return string
     */
    private function cacheKey(Route $route, array $args) : string
    {
        switch (gettype($route->getController())) {
            case 'string':
                $controller = $route->getController();
                break;

            case 'array':
                $controller = implode('::', $route->getController());
                break;

            default:
                if (is_callable($route->getController())) {
                    $controller = 'callable_' . spl_object_hash($route->getController());
                } else {
                    throw new \LogicException('Unexpected type');
                }
        }

        return $controller . '(' . json_encode($args) . ')';
    }

    /**
     * @param Route $route
     * @param string $cacheKey
     *
     * @return string|bool
     */
    private function cacheGet(Route $route, string $cacheKey)
    {
        if ($route->getOption(AbstractRoute::OPTIONS_CACHE)) {
            if ($data = self::cache('OUTPUT')->get($cacheKey)) {
                foreach ($data['headers'] as $name => $header) {
                    self::response()->addHeader($name, $header);
                }

                return $data['output'];
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * @param Route $route
     * @param string $cacheKey
     * @param $return
     *
     * @return bool
     */
    private function cacheSet(Route $route, string $cacheKey, $return) : bool
    {
        if ($route->getOption(AbstractRoute::OPTIONS_CACHE)) {
            $second = $route->getOption(AbstractRoute::OPTIONS_CACHE);

            if (self::session()->isStarted()) {
                throw new \LogicException("Can't set a cache on a route that use session data");
            }

            self::response()->addHeader('Expires', gmdate('D, d M Y H:i:s', time() + $second) . ' GMT');
            self::response()->addHeader('Cache-Control', 'public, max-age=' . $second . ', must-revalidate');
            self::response()->addHeader('Pragma', 'public, max-age=' . $second . ', must-revalidate');
            self::response()->addHeader('Vary', 'Accept-Encoding');

            $data = [
                'output' => $return,
                'headers' => self::response()->getHeaders(),
            ];

            self::cache('OUTPUT')->set($cacheKey, $data, $second);
        }

        return true;
    }

    /**
     * @param string $vars
     * @param Route $route
     * @param array $args
     *
     * @return string
     */
    private function replaceDynamicArgs(string $vars, Route $route, array $args) : string
    {
        return preg_replace_callback('`<([A-Za-z_0-9]+)>`', function ($match) use ($route, $args) {
            if (!isset($args[$match[1]])) {
                throw new \InvalidArgumentException(sprintf(
                    "Invalid route name '%s', missing dynamics controler param '%s'",
                    $route->getName(),
                    $match[1]
                ));
            }

            return str_replace('/', '\\', $args[$match[1]]);
        }, $vars);
    }

    /**
     * @param Route $route
     * @param array $args
     *
     * @return string|array|\SimpleXMLElement
     */
    private function callController(Route $route, array $args)
    {
        $callback = $route->getController();

        $this->currentRoute = $route;

        // route args
        $args = array_merge($route->getArgs(), $args);
        $this->args = $args;

        // simple function
        if (is_callable($callback) && is_object($callback)) {
            return call_user_func_array($callback, [$args]);
        }

        // controller
        preg_match('`([A-Za-z_0-9\\\\<>]+)(?:::)?([A-Za-z_0-9_<>]+)?`', $callback, $find);

        $class = $this->replaceDynamicArgs($find[1], $route, $args);
        $method = isset($find[2]) ? $this->replaceDynamicArgs($find[2], $route, $args) : null;

        // replace class dynamic args
        if (!class_exists($class)) {
            throw new \BadMethodCallException(sprintf(
                "Can't load class '%s' on route '%s'",
                $class,
                $route->getName()
            ));
        }

        $controller = new $class();

        if (is_null($method)) {
            $method = 'get';

            if (self::request()->isAjax() && method_exists($controller, 'ajax')) {
                $method = 'ajax';
            } elseif (self::request()->getMethod() == 'POST' && method_exists($controller, 'post')) {
                $method = 'post';
            }
        }

        $ordererArgs = $this->mapArguments($controller, $method, $args);

        $event = new TimerEvent('router.mainController');
        $event->setData([
            'controller' => $class,
            'method' => $method,
            'data' => $ordererArgs,
        ]);

        $return = null;
        if (method_exists($controller, 'init')) {
            $return = call_user_func_array([$controller, 'init'], $ordererArgs);
        }

        if (is_null($return)) {
            $return = call_user_func_array([$controller, $method], $ordererArgs);
        }

        self::emit($event);

        return $return;
    }

    /**
     * @param AbstractController $controller
     * @param string $method
     * @param array $args
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    private function mapArguments(AbstractController $controller, string $method, array $args) : array
    {
        $return = [];

        $reflection = new \ReflectionClass($controller);
        if (!$reflection->hasMethod($method)) {
            throw new \InvalidArgumentException(sprintf(
                "Method '%s' does not exist on '%s' for route '%s'",
                $method,
                get_class($controller),
                $this->currentRoute->getName()
            ));
        }

        $method = $reflection->getMethod($method);

        foreach ($method->getParameters() as $parameter) {
            if (!isset($args[$parameter->getName()]) && $parameter->isOptional() === false) {
                throw new \InvalidArgumentException(sprintf(
                    "Missing mandatory arguments on controller definition '%s' on '%s'",
                    $parameter->getName(),
                    get_class($controller)
                ));
            }

            $return[$parameter->getName()] = null;

            if (array_key_exists($parameter->getName(), $args) && $args[$parameter->getName()] !== '') {
                $value = $args[$parameter->getName()];

                if ($parameter->getClass() && $parameter->getClass()->getName() == 'Cawa\Date\DateTime') {
                    $value = new DateTime($value);
                }

                $return[$parameter->getName()] = $value;
            } elseif (!array_key_exists($parameter->getName(), $args)) {
                $return[$parameter->getName()] = $parameter->getDefaultValue();
            }
        }

        return $return;
    }
}
