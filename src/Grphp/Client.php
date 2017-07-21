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
use Grphp\Client\Interceptors\Base as BaseInterceptor;
use Grphp\Client\Interceptors\Timer as TimerInterceptor;
use Grphp\Client\Interceptors\LinkerD\ContextPropagation as LinkerDContextInterceptor;

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
    /** @var array<BaseInterceptor> $interceptors */
    protected $interceptors = [];

    /**
     * @param string $clientClass
     * @param Client\Config|null $config
     */
    public function __construct($clientClass, Config $config)
    {
        $this->config = $config;
        $credentials = ChannelCredentials::createInsecure();
        $this->client = new $clientClass($config->hostname, [
            'credentials' => $credentials,
        ]);
        if ($this->config->useDefaultHooks) {
            $this->addInterceptor(new TimerInterceptor($this->config->hookOptions));
            $this->addInterceptor(new LinkerDContextInterceptor($this->config->hookOptions));
        }
    }

    /**
     * Issue the call to the server, wrapping with the given interceptors
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

        $interceptors = $this->interceptors;
        return $this->intercept($interceptors, $request, $method, $metadata, $options, function() use (&$interceptors, &$request, &$method, &$metadata, &$options)
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
     * Add an interceptor to the client interceptor registry
     *
     * @param BaseInterceptor $interceptor
     * @throws \InvalidArgumentException if the interceptor does not extend \Grphp\Interceptors\Base
     */
    public function addInterceptor(BaseInterceptor $interceptor)
    {
        if (is_a($interceptor, BaseInterceptor::class)) {
            $this->interceptors[] = $interceptor;
        } else {
            throw new \InvalidArgumentException("Interceptor does not extend \\Grphp\\Client\\Interceptors\\Base");
        }
    }

    /**
     * @return array
     */
    private function buildAuthenticationMetadata()
    {
        $authentication = Authentication\Builder::fromClientConfig($this->config);
        if ($authentication) {
            return $authentication->getMetadata();
        } else {
            return [];
        }
    }

    /**
     * Intercept the call with the registered interceptors for the client
     *
     * @param array $interceptors
     * @param \Google\Protobuf\Internal\Message $request
     * @param string $method
     * @param array $metadata
     * @param array $options
     * @param callable $callback
     * @return mixed
     */
    private function intercept(array &$interceptors, &$request, &$method, &$metadata, &$options, callable $callback)
    {
        $i = array_shift($interceptors);
        if ($i) {
            $i->setRequest($request);
            $i->setMethod($method);
            $i->setMetadata($metadata);
            $i->setOptions($options);
            return $i->call(function() use (&$i, &$interceptors, &$method, &$request, &$metadata, &$options, &$callback) {
                $metadata = $i->getMetadata();
                $request = $i->getRequest();
                if (count($interceptors) > 0) {
                    return $this->intercept($interceptors, $request, $method, $metadata, $options, $callback);
                } else {
                    return $callback();
                }
            });
        } else {
            return $callback();
        }
    }
}
