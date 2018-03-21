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
declare(strict_types = 1);

namespace Grphp;

use Google\Protobuf\Internal\Message;
use Grpc\BaseStub;
use Grphp\Client\Config;
use Grphp\Client\Interceptors\Base as BaseInterceptor;
use Grphp\Client\Interceptors\Timer as TimerInterceptor;
use Grphp\Client\Interceptors\LinkerD\ContextPropagation as LinkerDContextInterceptor;
use Grphp\Client\Request;
use Grphp\Client\Response;
use Grphp\Client\Strategy\H2Proxy\Config as H2ProxyConfig;
use Grphp\Client\Strategy\H2Proxy\Strategy as H2ProxyStrategy;
use Grphp\Client\Strategy\H2Proxy\StrategyFactory as H2ProxyStrategyFactory;

/**
 * Layers over gRPC client communication to provide extra response, header, and timing
 * information.
 *
 * @package Grphp
 */
class Client
{
    /** @var BaseStub $client */
    protected $client;
    /** @var Config $config */
    protected $config;
    /** @var array<BaseInterceptor> $interceptors */
    protected $interceptors = [];
    /** @var string */
    private $clientClassName;

    /**
     * @param string $clientClassName
     * @param Client\Config|null $config
     */
    public function __construct(string $clientClassName, Config $config)
    {
        $this->clientClassName = $clientClassName;
        $this->config = $config;

        if ($this->config->useDefaultInterceptors) {
            $this->addInterceptor(new TimerInterceptor());
            $this->addInterceptor(new LinkerDContextInterceptor());
        }
        $this->validateAndDetermineStrategy();
    }

    /**
     * Issue the call to the server, wrapping with the given interceptors
     *
     * @param Message $request
     * @param string $method
     * @param array $metadata
     * @param array $options
     * @return Response
     */
    public function call(Message $request, string $method, array $metadata = [], array $options = []): Response
    {
        $metadata = array_merge($this->buildAuthenticationMetadata(), $metadata);

        $interceptors = $this->interceptors;
        return $this->intercept($interceptors, $request, $method, $metadata, $options, function () use (
            &$interceptors,
            &$request,
            &$method,
            &$metadata,
            &$options
        ) {
            $strategy = $this->config->getStrategy();
            $request = new Request($this->config, $method, $request, $this->getClient(), $metadata, $options);
            return $strategy->execute($request);
        });
    }

    /**
     * Lazy-load/instantiate client instance and appropriate channel credentials
     * @return \Grpc\BaseStub
     */
    private function getClient()
    {
        if ($this->client === null) {
            $this->client = new $this->clientClassName($this->config->hostname, [
                'credentials' => \Grpc\ChannelCredentials::createInsecure(),
            ]);
        }

        return $this->client;
    }

    /**
     * Add an interceptor to the client interceptor registry
     *
     * @param BaseInterceptor $interceptor
     * @throws \InvalidArgumentException if the interceptor does not extend \Grphp\Interceptors\Base
     */
    public function addInterceptor(BaseInterceptor $interceptor)
    {
        $this->interceptors[] = $interceptor;
    }

    /**
     * @return array<BaseInterceptor> An array of all interceptor objects assigned to this client
     */
    public function getInterceptors(): array
    {
        return $this->interceptors;
    }

    /**
     * Clears all interceptors on the client
     *
     * @return void
     */
    public function clearInterceptors()
    {
        $this->interceptors = [];
    }

    /**
     * @return array
     */
    private function buildAuthenticationMetadata(): array
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
        /** @var Client\Interceptors\Base $i */
        $i = array_shift($interceptors);
        if ($i) {
            $i->setRequest($request);
            $i->setMethod($method);
            $i->setMetadata($metadata);
            $client = $this->getClient();
            $i->setStub($client);
            return $i->call(function () use (
                &$i,
                &$interceptors,
                &$method,
                &$request,
                &$metadata,
                &$options,
                &$callback
            ) {
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

    /**
     * Load stubs and use h2proxy strategy if the C extension is not loaded
     */
    private function validateAndDetermineStrategy()
    {
        if (extension_loaded('grpc')) {
            return;
        }

        require_once dirname(__FILE__) . '/grpc.stubs.php';

        // force the h2proxy strategy
        $strategy = $this->config->getStrategy();
        if (!is_a($strategy, H2ProxyStrategy::class)) {
            $h2ProxyConfig = new H2ProxyConfig();
            $h2ProxyStrategy = (new H2ProxyStrategyFactory($h2ProxyConfig))->build();
            $this->config->setStrategy($h2ProxyStrategy);
        }
    }
}
