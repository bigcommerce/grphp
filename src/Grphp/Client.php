<?php
/**
 * Copyright (c) 2017-present, BigCommerce Pty. Ltd. All rights reserved
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Grphp;

use Grpc\ChannelCredentials;
use Grphp\Client\Config;
use Grphp\Instrumentation\Base as BaseInstrumentor;

/**
 * Layers over gRPC client communication to provide extra response, header, and timing
 * information.
 *
 * @package Grphp
 */
class Client
{
    /** @var \Grpc\BaseStub $client */
    protected $client;
    /** @var Config $config */
    protected $config;
    /** @var array<BaseInstrumentor> $instrumentors */
    protected $instrumentors = [];

    /**
     * @param string $clientClass
     * @param Client\Config|null $config
     */
    public function __construct($clientClass, Config $config)
    {
        $this->config = $config;
        $this->client = new $clientClass($config->hostname, [
            'credentials' => ChannelCredentials::createInsecure(),
        ]);
    }

    /**
     * Issue the call to the server, wrapping with the given instrumentors
     *
     * @param \Google\Protobuf\Internal\Message $request
     * @param string $method
     * @param array $metadata
     * @param array $options
     * @return Client\Response
     * @throws Client\Error
     */
    public function call($request, $method, array $metadata = [], array $options = [])
    {
        $metadata = array_merge($this->buildAuthenticationMetadata(), $metadata);
        $instrumentors = $this->instrumentors;
        return $this->instrument($instrumentors, $request, $method, $metadata, $options, function() use (&$instrumentors, &$request, &$method, &$metadata, &$options)
        {
            list($resp, $status) = $this->client->$method($request, $metadata, $options)->wait();
            if (!is_null($resp)) {
                $response = new Client\Response($resp, $status);
            } else {
                throw new Client\Error($this->config, $status);
            }
            return $response;
        });
    }

    /**
     * Instrument the call with the registered instrumentors for the client
     *
     * @param array $instrumentors
     * @param \Google\Protobuf\Internal\Message $request
     * @param string $method
     * @param array $metadata
     * @param array $options
     * @param callable $callback
     * @return mixed
     */
    public function instrument(array $instrumentors, $request, $method, $metadata, $options, callable $callback)
    {
        $i = array_shift($instrumentors);
        if ($i) {
            return $i->measure(function () use (&$instrumentors, &$method, &$request, &$metadata, &$options, &$callback) {
                if (count($instrumentors) > 0) {
                    return $this->instrument($instrumentors, $request, $method, $metadata, $options, $callback);
                } else {
                    return $callback();
                }
            });
        } else {
            return $callback();
        }
    }

    /**
     * Add an instrumentor to the client instrumentation registry
     *
     * @param BaseInstrumentor $instrumentor
     * @throws \InvalidArgumentException if the instrumentor does not extend \Grphp\Instrumentation\Base
     */
    public function addInstrumentor(BaseInstrumentor $instrumentor)
    {
        if (is_a($instrumentor, BaseInstrumentor::class)) {
            $this->instrumentors[] = $instrumentor;
        } else {
            throw new \InvalidArgumentException("Instrumentor does not extend \\Grphp\\Instrumentation\\Base");
        }
    }

    /**
     * @return array
     */
    public function buildAuthenticationMetadata()
    {
        $authentication = Authentication\Builder::fromClientConfig($this->config);
        if ($authentication) {
            return $authentication->getMetadata();
        } else {
            return [];
        }
    }
}
