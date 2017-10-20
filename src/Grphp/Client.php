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
use Grphp\Client\Channel;
use Grphp\Client\ClientFactory;
use Grphp\Client\Config;
use Grphp\Client\Error;
use Grphp\Client\Interceptors\Base as BaseInterceptor;
use Grphp\Client\Interceptors\Registry;
use Grphp\Client\Interceptors\Timer as TimerInterceptor;
use Grphp\Client\Interceptors\LinkerD\ContextPropagation as LinkerDContextInterceptor;
use Grphp\Client\Response;

/**
 * Layers over gRPC client communication to provide extra response, header, and timing
 * information.
 *
 * @package Grphp
 */
class Client
{
    /** @var Registry $interceptors */
    public $interceptors;

    /** @var BaseStub $client */
    private $client;
    /** @var Config $config */
    private $config;
    /** @var ClientFactory $clientFactory */
    private $clientFactory;

    /**
     * @param string $clientClass
     * @param Config $config
     * @param Channel $channel
     */
    public function __construct(string $clientClass, Config $config, Channel $channel = null)
    {
        $this->config = $config;
        $this->interceptors = new Registry();
        $this->clientFactory = new ClientFactory(
            $clientClass,
            $config->getHostname(),
            $channel,
            $config->getClientOptions()
        );
        if ($this->config->useDefaultInterceptors()) {
            $this->interceptors->add(new TimerInterceptor());
            $this->interceptors->add(new LinkerDContextInterceptor());
        }
    }

    /**
     * Issue the call to the server, wrapping with the given interceptors
     *
     * @param Message $request
     * @param string $method
     * @param array $metadata
     * @param array $options
     * @return Response
     * @throws Error
     */
    public function call(Message $request, string $method, array $metadata = [], array $options = []): Response
    {
        $client = $this->getClient();

        $interceptors = $this->interceptors->getAll();
        return $this->intercept($interceptors, $request, $method, $metadata, $options, function () use (
            &$client,
            &$interceptors,
            &$request,
            &$method,
            &$metadata,
            &$options
        ) {
            list($resp, $status) = $client->$method($request, $metadata, $options)->wait();
            if (!is_null($resp)) {
                $response = new Response($resp, $status);
            } else {
                throw new Error($this->config, $status);
            }
            return $response;
        });
    }

    /**
     * Intercept the call with the registered interceptors for the client
     *
     * @param BaseInterceptor[] $interceptors
     * @param Message $request
     * @param string $method
     * @param array $metadata
     * @param array $options
     * @param callable $callback
     * @return mixed
     */
    private function intercept(
        array &$interceptors,
        Message &$request,
        string &$method,
        array &$metadata,
        array &$options,
        callable $callback
    ) {
        /** @var Client\Interceptors\Base $i */
        $i = array_shift($interceptors);
        if ($i) {
            $i->setRequest($request);
            $i->setMethod($method);
            $i->setMetadata($metadata);
            $i->setStub($this->client);
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
     * Lazily create the client on the first initialized call
     * @return BaseStub
     */
    private function getClient(): BaseStub
    {
        if (!$this->client) {
            $this->client = $this->clientFactory->build();
        }
        return $this->client;
    }
}
