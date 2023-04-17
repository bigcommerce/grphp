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
use Grpc\ChannelCredentials;
use Grphp\Authentication\Builder as AuthBuilder;
use Grphp\Client\Config;
use Grphp\Client\Interceptors\Base as Interceptor;
use Grphp\Client\Interceptors\LinkerD\ContextPropagation as LinkerDContextInterceptor;
use Grphp\Client\Interceptors\Timer as TimerInterceptor;
use Grphp\Client\Request;
use Grphp\Client\Response;
use Grphp\Client\Strategy\Grpc\Strategy as GrpcStrategy;
use Grphp\Client\Strategy\H2Proxy\Config as H2ProxyConfig;
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
    /** @var array<Interceptor> $interceptors */
    protected array $interceptors = [];

    public function __construct(
        private string $clientClassName,
        protected Config $config
    ) {
        if ($this->config->useDefaultInterceptors) {
            $this->addInterceptor(new TimerInterceptor());
            $this->addInterceptor(new LinkerDContextInterceptor());
        }
        $this->validateAndDetermineStrategy();
    }

    /**
     * Issue the call to the server, wrapping with the given interceptors
     */
    public function call(Message $request, string $method, array $metadata = [], array $options = []): Response
    {
        $metadata = array_merge($this->buildAuthenticationMetadata(), $metadata);

        return $this->intercept($this->interceptors, $request, $method, $metadata, $options, fn () =>
            $this->config->getStrategy()->execute(new Request(
                $this->config,
                $method,
                $request,
                $this->getClient(),
                $metadata,
                $options
            )));
    }

    /**
     * Lazy-load/instantiate client instance and appropriate channel credentials
     */
    protected function getClient(): BaseStub
    {
        if ($this->client === null) {
            $this->client = new $this->clientClassName($this->config->hostname, [
                'credentials' => ChannelCredentials::createInsecure(),
            ]);
        }

        return $this->client;
    }

    /**
     * Add an interceptor to the client interceptor registry
     */
    public function addInterceptor(Interceptor $interceptor): void
    {
        $this->interceptors[] = $interceptor;
    }

    /**
     * @return array<Interceptor> An array of all interceptor objects assigned to this client
     */
    public function getInterceptors(): array
    {
        return $this->interceptors;
    }

    /**
     * Clears all interceptors on the client
     */
    public function clearInterceptors(): void
    {
        $this->interceptors = [];
    }

    private function buildAuthenticationMetadata(): array
    {
        $authentication = AuthBuilder::fromClientConfig($this->config);
        if ($authentication) {
            return $authentication->getMetadata();
        }

        return [];
    }

    /**
     * Intercept the call with the registered interceptors for the client
     *
     * @param array<Interceptor> $interceptors
     * @param callable(): Response $callback
     */
    private function intercept(
        array $interceptors,
        Message $request,
        string $method,
        array $metadata,
        array $options,
        callable $callback
    ): Response {
        $i = array_shift($interceptors);
        if (!$i) {
            return $callback();
        }

        $i->setRequest($request);
        $i->setMethod($method);
        $i->setMetadata($metadata);
        $client = $this->getClient();
        $i->setStub($client);

        return $i->call(fn () =>
            $this->intercept($interceptors, $i->getRequest(), $method, $i->getMetadata(), $options, $callback));
    }

    /**
     * Load stubs and use h2proxy strategy if the C extension is not loaded and grpc strategy is set
     */
    private function validateAndDetermineStrategy(): void
    {
        if (extension_loaded('grpc')) {
            return;
        }

        require_once dirname(__FILE__) . '/grpc.stubs.php';

        // if grpc extension is not loaded but is set to be used, force the h2proxy strategy instead
        $strategy = $this->config->getStrategy();
        if (is_a($strategy, GrpcStrategy::class)) {
            $h2ProxyConfig = new H2ProxyConfig();
            $h2ProxyStrategy = (new H2ProxyStrategyFactory($h2ProxyConfig))->build();
            $this->config->setStrategy($h2ProxyStrategy);
        }
    }
}
