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
use Grphp\Client\Response;
use Grphp\Test\GetThingReq;
use Grphp\Test\GetThingResp;
use Grphp\Test\TestInterceptor;
use Grphp\Test\Thing;
use Grphp\Test\ThingsClient;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    /** @var Config $clientConfig */
    protected $clientConfig;
    /** @var Client $client */
    protected $client;

    private static function createClientConfig(array $options = []): Config
    {
        return new Config(['hostname' => '0.0.0.0:9000'] + $options);
    }

    private static function createClient(Config $clientConfig): Client
    {
        return new Client(ThingsClient::class, $clientConfig);
    }

    public function setUp()
    {
        $this->client = static::createClient(static::createClientConfig());
    }

    public function testClearInterceptors()
    {
        $i = new TestInterceptor();
        $this->client->addInterceptor($i);
        $this->client->clearInterceptors();
        $this->assertCount(0, $this->client->getInterceptors());
    }

    public function testAddInterceptor()
    {
        $i = new TestInterceptor();
        $this->client->addInterceptor($i);
        $this->assertContains($i, $this->client->getInterceptors());
    }

    /**
     * @depends testClearInterceptors
     * @depends testAddInterceptor
     */
    public function testGetInterceptors()
    {
        $this->client->clearInterceptors();
        $i1 = new TestInterceptor();
        $this->client->addInterceptor($i1);
        $i1 = new TestInterceptor();
        $this->client->addInterceptor($i1);

        $interceptors = $this->client->getInterceptors();
        $this->assertCount(2, $interceptors);
    }

    /**
     * Test the call method
     *
     * @dataProvider providerCall
     */
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
        $this->assertEquals($opts['response_metadata'], $resp->getStatus()->metadata);

        /** @var GetThingResp $message */
        $message = $resp->getResponse();
        $this->assertInstanceOf(GetThingResp::class, $message);

        /** @var Thing $thing */
        $thing = $message->getThing();
        $this->assertInstanceOf(Thing::class, $thing);
        $this->assertEquals($id, $thing->getId());
        $this->assertEquals('Foo', $thing->getName());
    }

    public function providerCall()
    {
        return [
            [123, true, 0, 'Whoa now', ['one' => 'two']],
            [123, false, 8, 'Whoa now', ['one' => 'two']],
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
            $this->assertAttributeSame($e->getConfig(), 'config', $this->client);
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
        $client = static::createClient(
            static::createClientConfig([
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

    public function testClientIsNotInstantiatedOnConstruct()
    {
        static::assertAttributeEmpty('client', $this->client);
    }
}
