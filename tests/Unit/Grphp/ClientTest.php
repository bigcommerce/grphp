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
namespace Grphp\Test;

use Grphp\Authentication\Basic;
use Grphp\Client;
use Grphp\Client\Config;
use Grphp\Client\Error;
use Grphp\Client\Interceptors\Base as BaseInterceptor;
use Grphp\Client\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class ClientTest extends TestCase
{
    use ProphecyTrait;

    /** @var Config $clientConfig */
    protected $clientConfig;
    /** @var Client $client */
    protected $client;

    private function createClientConfig(array $options = []): Config
    {
        return new Config(['hostname' => '0.0.0.0:9000'] + $options);
    }

    private function createClient(Config $clientConfig): Client
    {
        return new Client(ThingsClient::class, $clientConfig);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient($this->createClientConfig());
    }

    public function testClearInterceptors()
    {
        $interceptorProphecy = $this->prophesize(BaseInterceptor::class);

        $this->client->addInterceptor($interceptorProphecy->reveal());
        $this->client->clearInterceptors();
        $this->assertCount(0, $this->client->getInterceptors());
    }

    public function testAddInterceptor()
    {
        $interceptorProphecy = $this->prophesize(BaseInterceptor::class);
        $interceptorMock = $interceptorProphecy->reveal();

        $this->client->addInterceptor($interceptorMock);
        $this->assertContains($interceptorMock, $this->client->getInterceptors());
    }

    #[Depends('testClearInterceptors')]
    #[Depends('testAddInterceptor')]
    public function testGetInterceptors()
    {
        $this->client->clearInterceptors();

        $this->client->addInterceptor($this->prophesize(BaseInterceptor::class)->reveal());
        $this->client->addInterceptor($this->prophesize(BaseInterceptor::class)->reveal());

        $this->assertCount(2, $this->client->getInterceptors());
    }

    /**
     * Test the call method
     */
    #[DataProvider('providerCall')]
    public function testCall($id, $isSuccess = true, $responseCode = 0, $responseDetails = '', $responseMetadata = [])
    {
        $opts = [
            'response_code' => $responseCode,
            'response_details' => $responseDetails,
            'response_metadata' => $responseMetadata,
        ];
        $req = new GetThingReq();
        $req->setId($id);
        $resp = $this->client->call($req, 'GetThing', [], $opts);
        $this->assertInstanceOf(Response::class, $resp);
        $this->assertEquals($isSuccess, $resp->isSuccess());
        $this->assertEquals($opts['response_code'], $resp->getStatusCode());
        $this->assertEquals($opts['response_details'], $resp->getStatusDetails());
        $this->assertEquals($opts['response_metadata'], $resp->getStatus()->getHeaders()->toArray());

        /** @var GetThingResp $message */
        $message = $resp->getResponse();
        $this->assertInstanceOf(GetThingResp::class, $message);

        /** @var Thing $thing */
        $thing = $message->getThing();
        $this->assertInstanceOf(Thing::class, $thing);
        $this->assertEquals($id, $thing->getId());
        $this->assertEquals('Foo', $thing->getName());
    }

    public static function providerCall()
    {
        return [
            [123, true, 0, 'Whoa now', ['one' => ['two']]],
            [123, false, 8, 'Whoa now', ['one' => ['two']]],
        ];
    }

    /**
     * Test error handling on a call
     */
    public function testCallError()
    {
        $req = new GetThingReq();
        $req->setId(0);
        try {
            $this->client->call($req, 'GetThing', [], [
                'response_null' => true,
                'response_code' => 9,
                'response_details' => 'foo',
            ]);
        } catch (Error $e) {
            $this->assertEquals(new Config([
                'hostname' => '0.0.0.0:9000',
            ]), $e->getConfig());
            $this->assertEquals('foo', $e->getDetails());
            $this->assertEquals(9, $e->getStatusCode());
            $this->assertNull($e->getTrailer());
        }
    }

    /**
     * Test the call method with auth provided
     */
    public function testCallWithAuth()
    {
        $client = $this->createClient(
            $this->createClientConfig([
                'authentication' => new Basic([
                    'username' => 'foo',
                    'password' => 'bar',
                ]),
            ])
        );

        $req = new GetThingReq();
        $req->setId(123);
        $resp = $client->call($req, 'GetThing', [], []);
        $this->assertInstanceOf(Response::class, $resp);
        $this->assertEquals(true, $resp->isSuccess());

        /** @var GetThingResp $message */
        $message = $resp->getResponse();
        $this->assertInstanceOf(GetThingResp::class, $message);

        /** @var Thing $thing */
        $thing = $message->getThing();
        $this->assertInstanceOf(Thing::class, $thing);
    }

    public function testCallInstantiatesClient()
    {
        $getThingReq = new GetThingReq();
        $getThingReq->setId(123);

        $response = $this->client->call($getThingReq, 'GetThing', [], []);

        $expectedResponse = new GetThingResp();
        $thing = new Thing();
        $thing->setId(123);
        $thing->setName('Foo');
        $expectedResponse->setThing($thing);

        $this->assertEquals(0, $response->getStatusCode());
        $this->assertEquals($expectedResponse, $response->getResponse());
    }

    public function testInterceptors()
    {
        $subject = new Client(
            ThingsClient::class,
            $this->createClientConfig(['use_default_interceptors' => false])
        );

        $interceptor1 = new TestInterceptor();
        $interceptor2 = new TestInterceptor();

        $subject->addInterceptor($interceptor1);
        $subject->addInterceptor($interceptor2);

        $opts = [
            'response_code' => 0,
            'response_details' => 'OK',
            'response_metadata' => ['x-metadata-key' => ['x-metadata-value']],
        ];

        $req = new GetThingReq();
        $req->setId(345);

        $resp = $subject->call($req, 'GetThing', [], $opts);

        $this->assertInstanceOf(Response::class, $resp);
        $this->assertEquals(true, $resp->isSuccess());
        $this->assertEquals(0, $resp->getStatusCode());
        $this->assertEquals('OK', $resp->getStatusDetails());
        $this->assertEquals(
            ['x-metadata-key' => ['x-metadata-value']],
            $resp->getStatus()->getHeaders()->toArray()
        );

        $this->assertTrue($interceptor1->hasBeenCalled());
        $this->assertTrue($interceptor2->hasBeenCalled());
    }
}
