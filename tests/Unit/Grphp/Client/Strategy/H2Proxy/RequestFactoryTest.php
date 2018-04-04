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

namespace Grphp\Client\Strategy\H2Proxy;

use Grphp\Client\Config;
use Grphp\Client\Request as RequestContext;
use Grphp\Client\Strategy\H2Proxy\Config as H2ProxyConfig;
use Grphp\Protobuf\Serializer;
use Grphp\Test\GetThingReq;
use Grphp\Test\ThingsClient;
use PHPUnit\Framework\TestCase;

final class RequestFactoryTest extends TestCase
{
    /** @var Config */
    private $config;
    /** @var H2ProxyConfig */
    private $proxyConfig;
    /** @var Serializer */
    private $serializer;
    /** @var RequestFactory */
    private $requestFactory;

    public function buildRequest($req = null, array $metadata = [], array $options = [])
    {
        $req = $req ?: new GetThingReq();
        $clientProphecy = $this->prophesize(ThingsClient::class);
        return new RequestContext($this->config, 'GetThing', $req, $clientProphecy->reveal(), $metadata, $options);
    }

    public function setUp()
    {
        parent::setUp();
        $this->config = new Config();
        $this->proxyConfig = new H2ProxyConfig();
        $this->serializer = new Serializer();
        $this->requestFactory = new RequestFactory($this->proxyConfig, $this->serializer);
    }

    public function testBuild()
    {
        $requestContext = $this->buildRequest();
        $httpRequest = $this->requestFactory->build($requestContext);
        static::assertInstanceOf(Request::class, $httpRequest);

        $headers = $httpRequest->getHeaders();
        static::assertEquals('h2c', $headers->get('Upgrade')->getValuesAsString());
        static::assertEquals('Upgrade', $headers->get('Connection')->getValuesAsString());
        static::assertEquals('application/grpc+proto', $headers->get('Content-Type')->getValuesAsString());
        static::assertEquals('trailers', $headers->get('TE')->getValuesAsString());
        static::assertEquals('grphp/1.0.0', $headers->get('User-Agent')->getValuesAsString());
        static::assertEquals('identity', $headers->get('Grpc-Encoding')->getValuesAsString());

        $deadlineish = intval(microtime(true) + RequestContext::DEFAULT_TIMEOUT);
        static::assertEquals($deadlineish, intval($headers->get('Deadline')->getValuesAsString()));
    }

    public function testWithHeaders()
    {
        $requestContext = $this->buildRequest(null, [
            'Foo' => 'bar',
            'Array' => ['123', '456'],
        ]);
        $request = $this->requestFactory->build($requestContext);
        static::assertInstanceOf(Request::class, $request);

        $headers = $request->getHeaders();
        static::assertEquals('bar', $headers->get('Foo')->getValuesAsString());
        static::assertEquals('123,456', $headers->get('Array')->getValuesAsString());
    }

    public function testWithCustomDeadline()
    {
        $newDeadline = 5.2;

        $requestContext = $this->buildRequest(null, [], [
            'timeout' => $newDeadline,
        ]);
        $httpRequest = $this->requestFactory->build($requestContext);
        static::assertInstanceOf(Request::class, $httpRequest);

        $deadlineish = intval(microtime(true) + $newDeadline);
        $headers = $httpRequest->getHeaders();
        static::assertEquals($deadlineish, intval($headers->get('Deadline')->getValuesAsString()));
    }
}
