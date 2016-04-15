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

/**
 * Сáша frameworks tests
 *
 * @author tchiotludo <http://github.com/tchiotludo>
 */
namespace CawaTest\Uri;

use Cawa\Net\Uri;
use PHPUnit_Framework_TestCase as TestCase;

class UriTest extends TestCase
{
    /**
     * Test that parsing and composing a valid URI returns the same URI
     *
     * @param string $uriString
     * @dataProvider validUriStringProvider
     */
    public function testValidUri(string $uriString)
    {
        $uri = Uri::parse($uriString);
        $this->assertEquals($uriString, $uri->get(false, true));
    }

    /**
     * Test that invalid URI returns an exception
     *
     * @param string $uriString
     * @dataProvider invalidUriStringProvider
     */
    public function testInvalidUri(string $uriString)
    {
        $this->setExpectedException('InvalidArgumentException');
        new Uri($uriString);
    }

    /**
     * Test the fluent interface
     *
     * @param string $method
     * @param string $params
     * @dataProvider fluentInterfaceMethodProvider
     */
    public function testFluentInterface($method, $params)
    {
        $uri = new Uri;
        $ret = call_user_func_array([$uri, $method], $params);
        $this->assertSame($uri, $ret);
    }

    /**
     * Test the scheme extract
     *
     * @param string $uriString
     * @param array $parts
     * @dataProvider validUriStringProviderWithPart
     */
    public function testScheme(string $uriString, array $parts)
    {
        $uri = new Uri($uriString);
        $this->assertEquals($parts['scheme'], $uri->getScheme());
    }

    /**
     * Test is https
     *
     * @param string $uriString
     * @param array $parts
     * @dataProvider validUriStringProviderWithPart
     */
    public function testIsHttps(string $uriString, array $parts)
    {
        $uri = new Uri($uriString);
        if ($parts['scheme'] == 'https') {
            $this->assertTrue($uri->isHttps());
        } else {
            $this->assertFalse($uri->isHttps());
        }
    }

    /**
     * Test the user extract
     *
     * @param string $uriString
     * @param array $parts
     * @dataProvider validUriStringProviderWithPart
     */
    public function testUser(string $uriString, array $parts)
    {
        $uri = new Uri($uriString);
        if (isset($parts['user'])) {
            $this->assertEquals($parts['user'], $uri->getUser());
        } else {
            $this->assertNull($uri->getUser());
        }
    }

    /**
     * Test the pass extract
     *
     * @param string $uriString
     * @param array $parts
     * @dataProvider validUriStringProviderWithPart
     */
    public function testPass(string $uriString, array $parts)
    {
        $uri = new Uri($uriString);
        if (isset($parts['pass'])) {
            $this->assertEquals($parts['pass'], $uri->getPassword());
        } else {
            $this->assertNull($uri->getPassword());
        }
    }

    /**
     * Test the hostname extract
     *
     * @param string $uriString
     * @param array $parts
     * @dataProvider validUriStringProviderWithPart
     */
    public function testHost(string $uriString, array $parts)
    {
        $uri = new Uri($uriString);

        if (isset($parts['host'])) {
            $this->assertEquals($parts['host'], $uri->getHost());
        } else {
            $this->assertNull($uri->getHost());
        }
    }

    /**
     * Test the domain extract
     *
     * @param string $uriString
     * @param array $parts
     * @dataProvider validUriStringProviderWithPart
     */
    public function testDomain(string $uriString, array $parts)
    {
        $uri = new Uri($uriString);

        if (isset($parts['domain'])) {
            $this->assertEquals($parts['domain'], $uri->getDomain());
        } else {
            $this->assertNull($uri->getDomain());
        }
    }

    /**
     * Test the port extract
     *
     * @param string $uriString
     * @param array $parts
     * @dataProvider validUriStringProviderWithPart
     */
    public function testPort(string $uriString, array $parts)
    {
        $uri = new Uri($uriString);
        if (isset($parts['port'])) {
            $this->assertEquals($parts['port'], $uri->getPort());
        } else {
            $this->assertNull($uri->getPort());
        }
    }

    /**
     * Test the path extract
     *
     * @param string $uriString
     * @param array $parts
     * @dataProvider validUriStringProviderWithPart
     */
    public function testPath(string $uriString, array $parts)
    {
        $uri = new Uri($uriString);
        $this->assertEquals($parts['path'], $uri->getPath());
    }

    /**
     * Test the querystring extract
     *
     * @param string $uriString
     * @param array $parts
     * @dataProvider validUriStringProviderWithPart
     */
    public function testQueryString(string $uriString, array $parts)
    {
        $uri = new Uri($uriString);
        if (isset($parts['queryString'])) {
            $this->assertEquals($parts['queryString'], $uri->getQuerystring());
        } else {
            $this->assertNull($uri->getQuerystring());
        }
    }

    /**
     * Test the query  extract
     *
     * @param string $uriString
     * @param array $parts
     * @dataProvider validUriStringProviderWithPart
     */
    public function testQuery(string $uriString, array $parts)
    {
        $uri = new Uri($uriString);
        if (array_key_exists('query', $parts)) {
            $this->assertEquals($parts['query'], $uri->getQuery('query'));
        } else {
            $this->assertNull($uri->getQuerystring());
        }
    }

    /**
     * Test the query  extract
     *
     * @param string $uriString
     * @param array $parts
     * @dataProvider validUriStringProviderWithPart
     */
    public function testQueries(string $uriString, array $parts)
    {
        $uri = new Uri($uriString);
        if (array_key_exists('queries', $parts)) {
            $this->assertEquals($parts['queries'], $uri->getQueries());
        } else {
            $this->assertEmpty($uri->getQueries());
        }
    }

    /**
     * Test that we can use an array to set the query parameters
     *
     * @param array $data
     * @param string $querystring
     * @dataProvider queryParamsArrayProvider
     */
    public function testQueryFromArray(array $data, $querystring)
    {
        $uri = new Uri('http://example.com/');
        $uri->setQuerystring($data);
        $this->assertEquals($querystring, $uri->getQuerystring());
    }

    /**
     * Test that add an array to set the query parameters
     *
     * @param array $data
     * @param string $querystring
     * @dataProvider queryParamsArrayProvider
     */
    public function testAddQueryFromArray(array $data, $querystring)
    {
        $uri = new Uri('http://example.com/');
        foreach ($data as $name => $query) {
            $uri->addQueries([$name => $query]);
        }
        $this->assertEquals($querystring, $uri->getQuerystring());
    }

    /**
     * Test that we can use an array to remove the query parameters
     *
     * @param array $data
     * @dataProvider queryParamsArrayProvider
     */
    public function testRemoveQueryFromArray(array $data)
    {
        $uri = new Uri('http://example.com/');
        foreach ($data as $name => $query) {
            $uri->removeQueries([$name]);
        }
        $this->assertNull($uri->getQuerystring());
    }

    /**
     * Test InvalidArgumentException on removeQueries
     */
    public function testRemoveQuerystringException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $uri = new Uri('http://example.com/?test=1');
        $uri->removeQueries([1]);
    }

    /**
     * Test InvalidArgumentException on removeQueries
     */
    public function testAddQuerystringException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $uri = new Uri('http://example.com/');
        $uri->addQueries([1 => 'test']);
    }

    /**
     * Test the fragment extract
     *
     * @param string $uriString
     * @param array $parts
     * @dataProvider validUriStringProviderWithPart
     */
    public function testFragment(string $uriString, array $parts)
    {
        $uri = new Uri($uriString);
        if (isset($parts['fragment'])) {
            $this->assertEquals($parts['fragment'], $uri->getFragment());
        } else {
            $this->assertNull($uri->getFragment());
        }
    }

    /**
     * Data provider for valid URIs, not necessarily complete
     *
     * @return array
     */
    public function validUriStringProvider()
    {
        return [
            ['http://www.example.com'],
            ['https://example.com:10082/foo/bar?query'],
            ['https://example.com:10082/foo/bar?query=1'],
            ['https://example.com:10082/foo/bar?query=1#fragment'],
            ['https://example.com:10082/foo/bar#fragmentOnly'],
            ['http://a_.!~*\'(-)n0123Di%25%26:pass;:&=+$,word@www.example.com'],
            ['http://[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:80/index.html'],
            ['http://[1080::8:800:200C:417A]/foo'],
            ['http://[::192.9.5.5]/ipng'],
            ['http://[::FFFF:129.144.52.38]:80/index.html'],
            ['http://[2620:0:1cfe:face:b00c::3]/'],
            ['http://[2010:836B:4179::836B:4179]'],
            ['http://www.example.org:80'],
            ['http://foo'],
            ['ftp://user:pass@example.org/'],
            ['http://1.1.1.1/'],
            ['http://[::1]/'],
            ['file:///etc/group'],
        ];
    }

    /**
     * Data provider for valid URIs, not necessarily complete
     *
     * @return array
     */
    public function invalidUriStringProvider()
    {
        return [
            ['://www.example.com'],
            ['https://example.com:80000/foo/bar?query'],
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
            ['setScheme', ['http']],
            ['setUser', ['user']],
            ['setPassword', ['password']],
            ['setHost', ['host']],
            ['setPort', [80]],
            ['setPath', ['/path']],
            ['setQuery', []],
            ['setQuery', ['file=name']],
            ['setQueries', [['file' => 'name']]],
            ['addQueries', [['file' => 'name']]],
            ['removeQueries', [['file' => 'name']]],
            ['removeAllQueries', []],
            ['setFragment', [null]],
            ['setFragment', ['fragment']],
        ];
    }

    /**
     * Data provider for valid URIs with their different parts
     *
     * @return array
     */
    public function validUriStringProviderWithPart()
    {
        return [
            ['ht-tp://server/path?query', [
                'scheme'   => 'ht-tp',
                'host'     => 'server',
                'domain'  => null,
                'path'     => '/path',
                'query'    =>  null,
                'queries' => ['query' => null],
                'queryString'    => 'query',
            ]],
            ['file:///foo/bar', [
                'scheme'   => 'file',
                'host'     => '',
                'domain'  => '',
                'path'     => '/foo/bar',
            ]],
            ['http://dude:lebowski@example.com/#fr/ag?me.nt', [
                'scheme'   => 'http',
                'user'     => 'dude',
                'pass'     => 'lebowski',
                'host'     => 'example.com',
                'domain'   => 'example.com',
                'path'     => '/',
                'fragment' => 'fr/ag?me.nt'
            ]],
            ['ftp://example.com:5555', [
                'scheme' => 'ftp',
                'host'   => 'example.com',
                'domain'  => 'example.com',
                'port'   => 5555,
                'path'   => ''
            ]],
            ['https://a.a.example.co.uk/foo//bar/baz//fob/?query=value', [
                'scheme'  => 'https',
                'host'    => 'a.a.example.co.uk',
                'domain'  => 'example.co.uk',
                'path'    => '/foo//bar/baz//fob/',
                'query'   => 'value',
                'queries' => ['query' => 'value'],
                'queryString'  => 'query=value',
            ]]
        ];
    }

    /**
     * Data provider for arrays of query string parameters, with the expected
     * query string
     *
     * @return array
     */
    public function queryParamsArrayProvider()
    {
        return [
            [[
                'foo' => 'bar',
                'baz' => 'waka'
            ], 'foo=bar&baz=waka'],
            [[
                'some key' => 'some crazy value?!#[]&=%+',
                'q1'        => ''
            ], 'some%20key=some%20crazy%20value%3F%21%23%5B%5D%26%3D%25%2B&q1='],
            [[
                'array'        => ['foo', 'bar', 'baz'],
                'otherstuff[]' => 1234
            ], 'array%5B0%5D=foo&array%5B1%5D=bar&array%5B2%5D=baz&otherstuff%5B%5D=1234']
        ];
    }
}
