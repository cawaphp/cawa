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

namespace Cawa\Http\Client;

use Cawa\Core\App;
use Cawa\Events\ManualTimerEvent;
use Cawa\Events\TimerEvent;
use Cawa\Http\Exceptions\ConnectionException;
use Cawa\Http\Exceptions\RequestException;
use Cawa\Http\Request;
use Cawa\Http\Response;

class Curl extends AbstractClient
{
    /**
     * @var resource
     */
    private $resource;

    /**
     * @param Request $request
     *
     * @throws ConnectionException
     * @throws RequestException
     *
     * @return Response
     */
    public function request(Request $request) : Response
    {
        if (!$this->resource) {
            $this->resource = curl_init();
        }

        // options
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT_MS => 5000,
            CURLOPT_CONNECTTIMEOUT_MS => 5000,
            CURLOPT_ENCODING => '', //force curl to send all Accept-Encoding available
        ];

        if (isset($this->options[self::OPTIONS_SSL_VERIFY]) && $this->options[self::OPTIONS_SSL_VERIFY] === false) {
            $options[CURLOPT_SSL_VERIFYHOST] = false;
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }

        if (!empty($this->options[self::OPTIONS_PROXY])) {
            $options[CURLOPT_PROXY] = $this->options[self::OPTIONS_PROXY];
        }

        // debug temporary files
        if (!empty($this->options[self::OPTIONS_DEBUG])) {
            $options[CURLOPT_VERBOSE] = true;
            $options[CURLOPT_STDERR] = fopen('php://temp', 'rw');
        }

        // headers handling
        $headers = array_merge($this->defaultHeader, $request->getHeaders());

        // add cookie to current headers
        if ($request->getCookies()) {
            $cookies = [];
            foreach ($request->getCookies() as $name => $cookie) {
                $cookies[] = $name . '=' . urlencode($cookie->getValue());
            }

            $headers['Cookie'] = implode('; ', $cookies);
        }

        if ($headers) {
            $finalHeaders = [];
            foreach ($headers as $name => $value) {
                $finalHeaders[] = $name . ': ' . $value;
            }
            $options[CURLOPT_HTTPHEADER] = $finalHeaders;
        }

        // handle post
        $options[CURLOPT_CUSTOMREQUEST] = $request->getMethod();
        if ($request->getMethod() != 'GET') {
            if ($request->getPayload()) {
                $options[CURLOPT_POSTFIELDS] = $request->getPayload();
            } elseif ($request->getHeader('Content-Type') == 'multipart/form-data') {
                // as an array(): The data will be sent as multipart/form-data
                // which is not always accepted by the serve

                // There are "@" issue on multipart POST requests.
                $options[CURLOPT_SAFE_UPLOAD] = true;
                $options[CURLOPT_POSTFIELDS] = $request->getPosts();
            } else {
                // as url encoded string: The data will be sent as application/x-www-form-urlencoded,
                // which is the default encoding for submitted html form data.
                $options[CURLOPT_POSTFIELDS] = http_build_query($request->getPosts());
            }
        }

        // final options
        $options[CURLOPT_URL] = $request->getUri()->get(false, true);

        curl_reset($this->resource);
        curl_setopt_array($this->resource, $options);

        // send request
        $requestEvent = new TimerEvent('httpClient.request');
        $response = curl_exec($this->resource);
        $infos = curl_getinfo($this->resource);

        // request event
        $requestEvent->addData([
            'method' => $request->getMethod(),
            'url' => $request->getUri()->get(false),
            'code' => $infos['http_code'],
            'header size' => $infos['header_size'],
            'request size' => $infos['request_size'],
        ]);
        App::events()->emit($requestEvent);

        // generate events for all duration
        $data = [
            'method' => $request->getMethod(),
            'url' => $request->getUri()->get(false),
            'code' => $infos['http_code']
        ];

        $event = new ManualTimerEvent('httpClient.nameLookup', $data);
        $event->setDuration($infos['namelookup_time'])
            ->setStart($requestEvent->getStart());
        App::events()->emit($event);

        $event = new ManualTimerEvent('httpClient.connect', $data);
        $event->setDuration($infos['connect_time'])
            ->setStart($requestEvent->getStart() + $infos['namelookup_time']);
        App::events()->emit($event);

        // debug log
        if (!empty($this->options[self::OPTIONS_DEBUG])) {
            rewind($options[CURLOPT_STDERR]);
            $log = stream_get_contents($options[CURLOPT_STDERR]);
            if ($response) {
                $log = $log . "\r\n" . htmlentities(explode("\r\n\r\n", $response)[1]);
            }
            App::logger()->debug($log);
        }

        // connection error
        if ($response === false) {
            $code = curl_errno($this->resource);
            $message = curl_error($this->resource);

            throw new ConnectionException($request, $message, $code);
        }

        // final response
        $response = new Response($response);

        if ($response->getStatus() >= 400) {
            throw new RequestException($request, $response, $response->getStatus());
        }

        return $response;
    }

    /**
     * close curl ressource
     */
    public function __destruct()
    {
        if ($this->resource) {
            curl_close($this->resource);
        }
    }
}
