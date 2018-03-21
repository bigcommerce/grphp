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

namespace Grphp\Client\Strategy\Grpc;

use Grpc\UnaryCall;
use Grphp\Client\Config;
use Grphp\Client\Error;
use Grphp\Client\Strategy\Grpc\Config as GrpcConfig;
use Grphp\Client\Request;
use Grphp\Client\Response;
use Grphp\Test\CallStatus;
use Grphp\Test\GetThingReq;
use Grphp\Test\GetThingResp;
use Grphp\Test\ThingsClient;
use PHPUnit\Framework\TestCase;

final class StrategyTest extends TestCase
{
    /** @var Config */
    private $config;
    /** @var GrpcConfig */
    private $grpcConfig;
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $requestProphecy;
    /** @var Strategy */
    private $strategy;

    public function setUp()
    {
        parent::setUp();
        $this->config = new Config();
        $this->grpcConfig = new GrpcConfig();
        $this->strategy = new Strategy($this->grpcConfig);
        $this->requestProphecy = $this->prophesize(Request::class);
    }

    public function testExecuteSuccess()
    {
        $resp = new GetThingResp();
        $req = new GetThingReq();
        $grpcStatus = new CallStatus(0, '', [
            'foo' => ['bar']
        ]);

        $unaryProphecy = $this->prophesize(UnaryCall::class);
        $unaryProphecy->wait()->willReturn([$resp, $grpcStatus]);
        $clientProphecy = $this->prophesize(ThingsClient::class);
        $clientProphecy->GetThing($req, [], [])->willReturn($unaryProphecy->reveal());
        $request = new Request($this->config, 'GetThing', $req, $clientProphecy->reveal(), [], []);

        $result = $this->strategy->execute($request);
        static::assertInstanceOf(Response::class, $result);
        static::assertEquals($result->getResponse(), $resp);

        $status = $result->getStatus();
        static::assertEquals($status->getCode(), $grpcStatus->code);
        static::assertEquals($status->getDetails(), $grpcStatus->details);
        static::assertEquals($status->getHeaders()->toArray(), $grpcStatus->metadata);
    }

    public function testExecuteFailure()
    {
        $req = new GetThingReq();

        $unaryProphecy = $this->prophesize(UnaryCall::class);
        $unaryProphecy->wait()->willReturn(null);
        $clientProphecy = $this->prophesize(ThingsClient::class);
        $clientProphecy->GetThing($req, [], [])->willReturn($unaryProphecy->reveal());
        $request = new Request($this->config, 'GetThing', $req, $clientProphecy->reveal(), [], []);

        static::expectException(Error::class);
        $this->strategy->execute($request);
    }
}
