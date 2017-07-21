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
namespace Grphp;

use Grphp\Test\BaseTest;
use Grphp\Test\GetThingReq;
use Grphp\Test\GetThingResp;
use Grphp\Test\TestInterceptor;

final class ClientTest extends BaseTest
{
    public function setUp()
    {
        $this->buildClient();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(Client::class, $this->client);
    }

    /**
     *
     */
    public function testClearInterceptors()
    {
        $i = new TestInterceptor();
        $this->client->addInterceptor($i);
        $this->client->clearInterceptors();
        $this->assertCount(0, $this->client->getInterceptors());
    }

    /**
     * @depends testConstructor
     */
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
        $this->assertInstanceOf(\Grphp\Client\Response::class, $resp);
        $this->assertEquals($isSuccess, $resp->isSuccess());
        $this->assertEquals($opts['response_code'], $resp->getStatusCode());
        $this->assertEquals($opts['response_details'], $resp->getStatusDetails());
        $this->assertEquals($opts['response_metadata'], $resp->getStatus()->metadata);

        /** @var \Grphp\Test\GetThingResp $message */
        $message = $resp->getResponse();
        $this->assertInstanceOf(\Grphp\Test\GetThingResp::class, $message);

        /** @var \Grphp\Test\Thing $thing */
        $thing = $message->getThing();
        $this->assertInstanceOf(\Grphp\Test\Thing::class, $thing);
        $this->assertEquals($id, $thing->getId());
        $this->assertEquals('Foo', $thing->getName());

        $ref = new \ReflectionClass($message);
    }
    public function providerCall()
    {
        return [
            [123, true, 0, 'Whoa now', ['one' => 'two']],
            [123, false, 8, 'Whoa now', ['one' => 'two']],
        ];
    }
}
