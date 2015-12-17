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

namespace Cawa\Http;

use Cawa\Http\Client\AbstractClient;
use Cawa\Http\Client\Curl;
use Cawa\Uri\Uri;

class Client
{
    /**
     * @var AbstractClient
     */
    private $client;

    /**
     * @return AbstractClient
     */
    public function getClient() : AbstractClient
    {
        return $this->client;
    }

    /**
     * @param AbstractClient|null $client
     */
    public function __construct(AbstractClient $client = null)
    {
        if ($client) {
            $this->client = $client;
        } else {
            $this->client = new Curl();
        }
    }

    /**
     * @var uri
     */
    private $baseUri;

    /**
     * @param string|uri $uri
     *
     * @return $this
     */
    public function setBaseUri($uri) : self
    {
        $this->baseUri = $uri instanceof Uri ? $uri : new Uri($uri);

        return $this;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function send(Request $request)
    {
        return $this->client->request($request);
    }

    /**
     * @param string $uri
     *
     * @return Request
     */
    private function getRequest($uri)
    {
        if ($this->baseUri && substr($uri, 0, 1) != '/') {
            throw new \InvalidArgumentException(
                sprintf("Invalid uri '%s' with baseUri '%s'", $uri, $this->baseUri->get(false))
            );
        }

        if ($this->baseUri && $uri) {
            $uri = clone $this->baseUri;
            $uri->setPath($uri->getPath() . $uri);
        } else {
            $uri = $uri instanceof Uri ? $uri : new Uri($uri);
        }

        return new Request($uri);
    }

    /**
     * @param string $uri
     *
     * @return Response
     */
    public function get(string $uri)
    {
        $request = $this->getRequest($uri);

        return $this->send($request);
    }

    /**
     * @param string $uri
     *
     * @return Response
     */
    public function head(string $uri)
    {
        $request = $this->getRequest($uri)
            ->setMethod('HEAD');

        return $this->send($request);
    }

    /**
     * @param string $uri
     * @param array $data
     *
     * @return Response
     */
    public function post(string $uri, array $data = [])
    {
        $request = $this->getRequest($uri)
            ->setMethod('POST')
            ->setPosts($data);

        return $this->send($request);
    }

    /**
     * @param string $uri
     * @param array $data
     *
     * @return Response
     */
    public function put(string $uri, array $data = [])
    {
        $request = $this->getRequest($uri)
            ->setMethod('PUT')
            ->setPosts($data);

        return $this->send($request);
    }

    /**
     * @param string $uri
     * @param array $data
     *
     * @return Response
     */
    public function patch(string $uri, array $data = [])
    {
        $request = $this->getRequest($uri)
            ->setMethod('PATCH')
            ->setPosts($data);

        return $this->send($request);
    }

    /**
     * @param string $uri
     *
     * @return Response
     */
    public function delete(string $uri)
    {
        $request = $this->getRequest($uri)
            ->setMethod('DELETE');

        return $this->send($request);
    }

    /**
     * @param string $uri
     *
     * @return Response
     */
    public function options(string $uri)
    {
        $request = $this->getRequest($uri)
            ->setMethod('OPTIONS');

        return $this->send($request);
    }

    /**
     * @param string $uri
     *
     * @return Response
     */
    public function trace(string $uri)
    {
        $request = $this->getRequest($uri)
            ->setMethod('TRACE');

        return $this->send($request);
    }
}
