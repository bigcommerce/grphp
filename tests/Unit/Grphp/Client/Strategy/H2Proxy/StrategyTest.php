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
namespace Grphp\Client\Strategy\H2Proxy;

use Exception;
use Grpc\UnaryCall;
use Grphp\Client\Error;
use Grphp\Client\Error\Status;
use Grphp\Client\HeaderCollection;
use Grphp\Client\Request;
use Grphp\Client\Response;
use Grphp\Client\Strategy\H2Proxy\Request as H2ProxyRequest;
use Grphp\Client\Strategy\H2Proxy\RequestException;
use Grphp\Client\Strategy\H2Proxy\Response as H2ProxyResponse;
use Grphp\Protobuf\Serializer;
use Grphp\Test\CallStatus;
use Grphp\Test\GetThingReq;
use Grphp\Test\GetThingResp;
use Grphp\Test\ThingsClient;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

final class StrategyTest extends TestCase
{
    private $executorProphecy;
    private $config;
    private $h2ProxyConfig;
    private $serializer;
    private $requestFactory;
    private $response;
    private $responseHeaders;
    private $responseStatus;

    public function setUp()
    {
        parent::setUp();
        $this->executorProphecy = $this->prophesize(RequestExecutor::class);

        $this->config = new \Grphp\Client\Config();
        $this->h2ProxyConfig = new Config();
        $this->serializer = new Serializer();
        $this->requestFactory = new RequestFactory($this->h2ProxyConfig, $this->serializer);
        $this->responseHeaders = new HeaderCollection();
        $this->responseStatus = new Status(Status::CODE_INTERNAL, 'FAIL', $this->responseHeaders);
        $this->response = new H2ProxyResponse('', $this->responseHeaders);
    }

    public function buildClientProphecy($req)
    {
        $resp = new GetThingResp();
        $grpcStatus = new CallStatus(0, '', [
            'foo' => ['bar']
        ]);
        $unaryProphecy = $this->prophesize(UnaryCall::class);
        $unaryProphecy->wait()->willReturn([$resp, $grpcStatus]);
        $clientProphecy = $this->prophesize(ThingsClient::class);
        $clientProphecy->GetThing($req, [], [])->willReturn($unaryProphecy->reveal());
        $clientProphecy->getExpectedResponseMessages()->willReturn([
            'getThing' => '\Grphp\Test\GetThingResp',
        ]);
        return $clientProphecy;
    }

    public function buildClientRequest($clientProphecy, $req)
    {
        return new Request($this->config, 'GetThing', $req, $clientProphecy->reveal(), [], []);
    }

    public function testSuccessfulExecution()
    {
        $req = new GetThingReq();
        $clientProphecy = $this->buildClientProphecy($req);
        $clientRequest = $this->buildClientRequest($clientProphecy, $req);

        $this->executorProphecy->send(Argument::type(H2ProxyRequest::class))->willReturn($this->response);

        $strategy = new Strategy($this->serializer, $this->requestFactory, $this->executorProphecy->reveal());
        $response = $strategy->execute($clientRequest);
        static::assertInstanceOf(Response::class, $response);
    }

    public function testFailedExecution()
    {
        $req = new GetThingReq();
        $clientProphecy = $this->buildClientProphecy($req);
        $clientRequest = $this->buildClientRequest($clientProphecy, $req);

        $exception = new Error($this->config, $this->responseStatus);
        $this->executorProphecy->send(Argument::type(H2ProxyRequest::class))->willThrow($exception);

        $strategy = new Strategy($this->serializer, $this->requestFactory, $this->executorProphecy->reveal());
        static::expectException(Error::class);
        $strategy->execute($clientRequest);
    }

    public function testExecutePropagatesGrpcStatusCodeInTheException()
    {
        $headers = new HeaderCollection();
        $headers->add('grpc-status', 16);
        $headers->add('grpc-message', 'Unathorized');
        $body = 'Unauthorized, sorry!';

        $request = $this->prophesize(Request::class);
        $request->getPath()->willReturn("foo.barService/BazCall");

        $config = $this->config;
        $request->fail(Argument::allOf(Argument::Type(Status::class), Argument::which('getCode', 16)))
            ->will(function (array $args) use ($config) {
                throw new Error($config, $args[0]);
            });

        $h2Request = $this->prophesize(H2ProxyRequest::class);

        $serializer = $this->prophesize(Serializer::class);
        $requestFactory = $this->prophesize(RequestFactory::class);
        $requestFactory->build($request->reveal())->willReturn($h2Request->reveal());

        $exception = new RequestException($body, $headers);

        $this->executorProphecy->send($h2Request->reveal())->willThrow($exception);

        $subject = new Strategy(
            $serializer->reveal(),
            $requestFactory->reveal(),
            $this->executorProphecy->reveal()
        );

        $this->expectException(Error::class);
        $this->expectExceptionMessage('Unathorized');
        $this->expectExceptionCode(16);

        $subject->execute($request->reveal());
    }

    public function testExecuteUsesUnknownCodeIfGrpcStatusHeaderIsMissing()
    {
        $headers = new HeaderCollection();
        $body = 'Everything is on fire!';

        $request = $this->prophesize(Request::class);
        $request->getPath()->willReturn("foo.bar.bazService/GetThing");

        $config = $this->config;
        $request->fail(Argument::allOf(Argument::Type(Status::class), Argument::which('getCode', 2)))
            ->will(function (array $args) use ($config) {
                throw new Error($config, $args[0]);
            });

        $h2Request = $this->prophesize(H2ProxyRequest::class);

        $serializer = $this->prophesize(Serializer::class);
        $requestFactory = $this->prophesize(RequestFactory::class);
        $requestFactory->build($request->reveal())->willReturn($h2Request->reveal());

        $exception = new RequestException($body, $headers);

        $this->executorProphecy->send($h2Request->reveal())->willThrow($exception);

        $subject = new Strategy(
            $serializer->reveal(),
            $requestFactory->reveal(),
            $this->executorProphecy->reveal()
        );

        $this->expectException(Error::class);
        $this->expectExceptionMessage('Everything is on fire!');
        $this->expectExceptionCode(2);

        $subject->execute($request->reveal());
    }
}
