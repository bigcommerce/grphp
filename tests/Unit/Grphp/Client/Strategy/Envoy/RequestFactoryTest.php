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
declare(strict_types=1);

namespace Unit\Grphp\Client\Strategy\Envoy;

use Grphp\Client\Config;
use Grphp\Client\Request as RequestContext;
use Grphp\Client\Strategy\Envoy\Config as EnvoyConfig;
use Grphp\Client\Strategy\Envoy\Request;
use Grphp\Client\Strategy\Envoy\RequestFactory;
use Grphp\Protobuf\Serializer;
use Grphp\Test\GetThingReq;
use Grphp\Test\ThingsClient;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class RequestFactoryTest extends TestCase
{
    use ProphecyTrait;

    private Config $config;
    private RequestFactory $requestFactory;

    public function buildRequest($req = null, array $metadata = [], array $options = [])
    {
        $req = $req ?: new GetThingReq();
        $clientProphecy = $this->prophesize(ThingsClient::class);
        return new RequestContext($this->config, 'GetThing', $req, $clientProphecy->reveal(), $metadata, $options);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new Config();
        $this->requestFactory = new RequestFactory(new EnvoyConfig(), new Serializer());
    }

    public function testBuild()
    {
        $requestContext = $this->buildRequest();
        $httpRequest = $this->requestFactory->build($requestContext);
        $this->assertInstanceOf(Request::class, $httpRequest);

        $headers = $httpRequest->getHeaders();
        $this->assertEquals('application/grpc', $headers->get('Content-Type')->getValuesAsString());
        $this->assertEquals('grphp/1.0.0', $headers->get('User-Agent')->getValuesAsString());
        $this->assertSame('trailers', $headers->get('TE')->getValuesAsString());

        $deadlineish = intval(microtime(true) + RequestContext::DEFAULT_TIMEOUT);
        $this->assertEquals($deadlineish, intval($headers->get('Deadline')->getValuesAsString()));
        $this->assertEquals(EnvoyConfig::DEFAULT_TIMEOUT, $httpRequest->getTimeout());
    }

    public function testBuildWithTimeout(): void
    {
        $requestContext = $this->buildRequest(null, [], ['timeout' => 10]);
        $request = $this->requestFactory->build($requestContext);
        $this->assertInstanceOf(Request::class, $request);
        $this->assertEquals(10, $request->getTimeout());
    }

    public function testWithHeaders()
    {
        $requestContext = $this->buildRequest(
            null,
            [
                'Foo' => 'bar',
                'Array' => ['123', '456'],
            ]
        );
        $request = $this->requestFactory->build($requestContext);
        $this->assertInstanceOf(Request::class, $request);

        $headers = $request->getHeaders();
        $this->assertEquals('bar', $headers->get('Foo')->getValuesAsString());
        $this->assertEquals('123,456', $headers->get('Array')->getValuesAsString());
    }

    public function testWithCustomDeadline()
    {
        $newDeadline = 5.2;

        $requestContext = $this->buildRequest(
            null,
            [],
            [
                'timeout' => $newDeadline,
            ]
        );
        $httpRequest = $this->requestFactory->build($requestContext);
        $this->assertInstanceOf(Request::class, $httpRequest);

        $deadlineish = intval(microtime(true) + $newDeadline);
        $headers = $httpRequest->getHeaders();
        $this->assertEquals($deadlineish, intval($headers->get('Deadline')->getValuesAsString()));
    }
}
