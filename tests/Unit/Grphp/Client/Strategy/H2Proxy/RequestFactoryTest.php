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

namespace Unit\Grphp\Client\Strategy\H2Proxy;

use Grphp\Client\Config;
use Grphp\Client\Request as RequestContext;
use Grphp\Client\Strategy\H2Proxy\Config as H2ProxyConfig;
use Grphp\Client\Strategy\H2Proxy\Request;
use Grphp\Client\Strategy\H2Proxy\RequestFactory;
use Grphp\Protobuf\Serializer;
use Grphp\Test\GetThingReq;
use Grphp\Test\ThingsClient;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class RequestFactoryTest extends TestCase
{
    use ProphecyTrait;

    private Config $config;
    private H2ProxyConfig $proxyConfig;
    private Serializer $serializer;
    private RequestFactory  $requestFactory;

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
        $this->proxyConfig = new H2ProxyConfig();
        $this->serializer = new Serializer();
        $this->requestFactory = new RequestFactory($this->proxyConfig, $this->serializer);
    }

    public function testBuild()
    {
        $requestContext = $this->buildRequest();
        $httpRequest = $this->requestFactory->build($requestContext);
        $this->assertInstanceOf(Request::class, $httpRequest);

        $headers = $httpRequest->getHeaders();
        $this->assertEquals('h2c', $headers->get('Upgrade')->getValuesAsString());
        $this->assertEquals('Upgrade', $headers->get('Connection')->getValuesAsString());
        $this->assertEquals('application/grpc+proto', $headers->get('Content-Type')->getValuesAsString());
        $this->assertEquals('trailers', $headers->get('TE')->getValuesAsString());
        $this->assertEquals('grphp/1.0.0', $headers->get('User-Agent')->getValuesAsString());
        $this->assertEquals('identity', $headers->get('Grpc-Encoding')->getValuesAsString());

        $deadlineish = intval(microtime(true) + RequestContext::DEFAULT_TIMEOUT);
        $this->assertEquals($deadlineish, intval($headers->get('Deadline')->getValuesAsString()));
    }

    public function testBuildWithTimeout(): void
    {
        $requestContext = $this->buildRequest(null, [], ['timeout' => 10]);
        $request = $this->requestFactory->build($requestContext);
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

    public function testWithoutProxy()
    {
        $serializer = $this->prophesize(Serializer::class);
        $serializer->serializeRequest(Argument::any())->willReturn('');
        /** @var H2ProxyConfig|ObjectProphecy $config */
        $config = $this->prophesize(H2ProxyConfig::class);
        $config->getBaseUri()->willReturn('service.com:5678');
        $config->getContentType()->willReturn(H2ProxyConfig::DEFAULT_CONTENT_TYPE);
        $config->getTimeout()->willReturn(15)->shouldBeCalled();

        /** @var RequestContext|ObjectProphecy $requestContext */
        $requestContext = $this->prophesize(RequestContext::class);
        $requestContext->getMetadata()->willReturn([]);
        $requestContext->buildDeadline()->willReturn(0);
        $requestContext->getPath()->willReturn('/espresso.machine/PullDoubleShot');
        $requestContext->getTimeout()->willReturn(null);

        $config->getProxyUri()->willReturn('')->shouldBeCalled();

        $subject = new RequestFactory($config->reveal(), $serializer->reveal());

        $request = $subject->build($requestContext->reveal());
        $this->assertEmpty($request->getProxyUri());
        $this->assertEquals(15, $request->getTimeout());
    }

    public function testWithProxy()
    {
        $serializer = $this->prophesize(Serializer::class);
        $serializer->serializeRequest(Argument::any())->willReturn('');
        /** @var H2ProxyConfig|ObjectProphecy $config */
        $config = $this->prophesize(H2ProxyConfig::class);
        $config->getBaseUri()->willReturn('service.com:5678');
        $config->getContentType()->willReturn(H2ProxyConfig::DEFAULT_CONTENT_TYPE);

        /** @var RequestContext|ObjectProphecy $requestContext */
        $requestContext = $this->prophesize(RequestContext::class);
        $requestContext->getMetadata()->willReturn([]);
        $requestContext->buildDeadline()->willReturn(0);
        $requestContext->getPath()->willReturn('/My.GRPC.Service/rpc');
        $requestContext->getTimeout()->willReturn(0.15);

        $config->getProxyUri()->willReturn('127.0.0.1:1234')->shouldBeCalled();

        $subject = new RequestFactory($config->reveal(), $serializer->reveal());

        $request = $subject->build($requestContext->reveal());
        $this->assertSame('127.0.0.1:1234', $request->getProxyUri());
        $this->assertSame(0.15, $request->getTimeout());
    }
}
