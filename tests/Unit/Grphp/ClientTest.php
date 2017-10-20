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

namespace Grphp\Test;

use Grphp\Authentication\Basic;
use Grphp\Client;
use Grphp\Client\Error;
use Grphp\Client\Response;
use Grphp\Test\Thing;

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
     * Test the call method
     *
     * @dataProvider providerCall
     * @param int $id
     * @param bool $isSuccess
     * @param int $responseCode
     * @param string $responseDetails
     * @param array $responseMetadata
     */
    public function testCall(int $id, bool $isSuccess = true, int $responseCode = 0, string $responseDetails = '', array $responseMetadata = [])
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

        /** @var \Grphp\Test\GetThingResp $message */
        $message = $resp->getResponse();
        $this->assertInstanceOf(GetThingResp::class, $message);

        /** @var Thing $thing */
        $thing = $message->getThing();
        $this->assertInstanceOf(Thing::class, $thing);
        $this->assertEquals($id, $thing->getId());
        $this->assertEquals('Foo', $thing->getName());
    }

    /**
     * @return array
     */
    public function providerCall(): array
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
            $this->assertEquals($this->clientConfig, $e->getConfig());
            $this->assertEquals('foo', $e->getDetails());
            $this->assertEquals(9, $e->getStatusCode());
            $this->assertNull($e->getTrailer());
        }
    }

    /**
     * Test the call method with auth provided
     *
     * @dataProvider providerCall
     */
    public function testCallWithAuth()
    {
        $client = $this->buildClient([
            'authentication' => 'basic',
            'authenticationOptions' => [
                'username' => 'foo',
                'password' => 'bar',
            ],
        ]);

        $req = new GetThingReq();
        $req->setId(123);
        $resp = $client->call($req, 'GetThing', [], []);
        $this->assertInstanceOf(Response::class, $resp);
        $this->assertEquals(true, $resp->isSuccess());

        /** @var \Grphp\Test\GetThingResp $message */
        $message = $resp->getResponse();
        $this->assertInstanceOf(GetThingResp::class, $message);

        /** @var Thing $thing */
        $thing = $message->getThing();
        $this->assertInstanceOf(Thing::class, $thing);
    }
}
