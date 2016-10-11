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

/**
 * Сáша frameworks tests
 *
 * @author tchiotludo <http://github.com/tchiotludo>
 */
namespace CawaTest\Router;

use Cawa\Router\Route;
use PHPUnit_Framework_TestCase as TestCase;

class RouteTest extends TestCase
{
    /**
     * Test the fluent interface
     *
     * @param string $method
     * @param string $params
     * @dataProvider fluentInterfaceMethodProvider
     */
    public function testFluentInterface($method, $params)
    {
        $route = new Route();
        $ret = call_user_func_array([$route, $method], $params);
        $this->assertSame($route, $ret);
    }

    /**
     * Test the fluent interface
     *
     * @param string $method
     * @param string $params
     * @param mixed $getParam
     * @dataProvider fluentInterfaceMethodProvider
     */
    public function testFluentInterfaceGetter($method, $params, $getParam = null)
    {
        $route = new Route();
        call_user_func_array([$route, $method], $params);
        $get = call_user_func_array([$route, 'g' . substr($method, 1)], $params);
        $this->assertSame($get, !is_null($getParam) ? $getParam : $params[0]);
    }

    /**
     * Test InvalidArgumentException on setMatch
     *
     * @dataProvider invalidMatchProvider
     */
    public function testSetMatchException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $route = new Route();
        $route->setMatch('^/fr$');
    }

    /**
     * Test Empty Option
     */
    public function testGetEmptyOption()
    {
        $route = new Route();
        $this->assertNull($route->getOption(Route::OPTIONS_CACHE));
    }

    /**
     * Data provider for valid URIs, not necessarily complete
     *
     * @return array
     */
    public function invalidMatchProvider()
    {
        return [
            ['^/fr$'],
            ['.*$'],
            ['$.*'],
        ];
    }

    /**
     * Return all methods that are expected to return the same object they
     * are called on, to test that the fluent interface is not broken
     *
     * @return array
     */
    public function fluentInterfaceMethodProvider()
    {
        return [
            ['setResponseCode', [200]],
            ['setName', ['name']],
            ['setMatch', ['/test']],
            ['setController', ['controller']],
            ['setOption', [Route::OPTIONS_URLIZE, false], false],
            ['setOptions', [[Route::OPTIONS_URLIZE, false]]],
        ];
    }
}
