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
namespace Grphp\Client;

use Google\Protobuf\Internal\Message;
use Grphp\Client\Error\Status;
use Grphp\Test\GetThingReq;
use Grphp\Test\GetThingResp;
use Grphp\Test\ThingsClient;
use Grphp\Test\DummyClient;
use Grphp\Test\DummyClientAgainClient;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class RequestTest extends TestCase
{
    use ProphecyTrait;

    /** @var Config */
    private $config;
    private $clientProphecy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new Config();
        $this->clientProphecy = $this->prophesize(ThingsClient::class);
    }

    /**
     * @param string $method
     * @param Message $message
     * @param array $metadata
     * @param array $options
     * @return Request
     */
    public function buildRequest($method = 'GetThing', $message = null, array $metadata = [], array $options = [])
    {
        $message = $message ?: new GetThingReq();
        return new Request($this->config, $method, $message, $this->clientProphecy->reveal(), $metadata, $options);
    }

    public function testGetters()
    {
        $method = 'GetThing';
        $message = new GetThingReq();
        $metadata = [
            'foo' => 'bar'
        ];
        $options = [
            'timeout' => 3
        ];

        $request = $this->buildRequest($method, $message, $metadata, $options);
        static::assertEquals($method, $request->getMethod());
        static::assertEquals($message, $request->getMessage());
        static::assertEquals($metadata, $request->getMetadata());
        static::assertEquals($options, $request->getOptions());
    }

    public function testGetExpectedResponseMessageClass()
    {
        // TODO: After PHP gRPC generator work is merged
        static::assertEquals(1, 1);
    }

    public function testGetPath()
    {
        $request = new Request($this->config, 'GetThings', null, new DummyClient());
        $path = $request->getPath();

        static::assertEquals('grphp.test.Dummy/GetThings', $path);
    }

    public function testBuildDefaultDeadline()
    {
        $request = $this->buildRequest();
        $expectedDeadlineish = intval(microtime(true) + Request::DEFAULT_TIMEOUT);
        static::assertEquals($expectedDeadlineish, intval($request->buildDeadline()));
    }

    public function testBuildCustomDeadline()
    {
        $newDeadline = 42;
        $request = $this->buildRequest('GetThing', null, [], [
            'timeout' => $newDeadline
        ]);
        $expectedDeadlineish = intval(microtime(true) + $newDeadline);
        static::assertEquals($expectedDeadlineish, intval($request->buildDeadline()));
    }

    public function testGetServiceName()
    {
        $request = new Request($this->config, 'GetThings', null, new DummyClient());
        static::assertEquals('grphp.test.Dummy', $request->getServiceName());

        $request = new Request($this->config, 'GetThings', null, new DummyClientAgainClient());
        static::assertEquals('grphp.test.DummyClientAgain', $request->getServiceName());
    }

    public function testFail()
    {
        $request = $this->buildRequest();

        $status = new Status(\Grpc\STATUS_INTERNAL, 'Foo');
        static::expectException(Error::class);
        $request->fail($status);
    }

    public function testSucceed()
    {
        $request = $this->buildRequest();
        $status = new Status(\Grpc\STATUS_OK, '');

        $responseMessage = new GetThingResp();
        $response = $request->succeed($responseMessage, $status);
        static::assertInstanceOf(Response::class, $response);
        static::assertEquals($responseMessage, $response->getResponse());
    }

    public function testGetTimeoutReturnsNullIfNoTimeoutSpecified()
    {
        $request = $this->buildRequest();

        $this->assertSame(null, $request->getTimeout());
    }

    public function testGetTimeoutReturnsTimeoutFromOptions()
    {
        $options = ['timeout' => 1.55];

        $request = $this->buildRequest('GetThings', null, [], $options);

        $this->assertSame(1.55, $request->getTimeout());
    }
}
