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

namespace Unit\Grphp\Client\Interceptors;

use Grphp\Client\Interceptors\ResponseMessageLookupFailedException;
use Grphp\Client\Interceptors\StubNotFoundException;
use Grphp\Test\GetThingReq;
use Grphp\Test\TestInterceptor;
use Grphp\Test\TestMetadataSetInterceptor;
use Grphp\Test\ThingsClient;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    public function testCall(): void
    {
        $interceptor = new TestInterceptor();
        $result = $interceptor->call(function () {
            return 42;
        });
        $this->assertSame($result, 42);
    }

    public function testMetadata(): void
    {
        $interceptor = new TestMetadataSetInterceptor();
        $result = $interceptor->call(function () {
            return 1;
        });
        $this->assertSame($result, 1);
        $md = $interceptor->getMetadata();
        $this->assertArrayHasKey('test', $md);
        $this->assertEquals('one', $md['test']);
    }

    // Happy path for getting a FQN of the RPC in question
    public function testGetExpectedResponseMessageClass(): void
    {
        $client = new ThingsClient('127.0.0.1:9000', [ 'credentials' => null ]);
        $method = 'getThing';
        $metadata = ['some' => 'value'];

        $interceptor = new TestInterceptor();
        $interceptor->setMethod($method);
        $interceptor->setMetadata($metadata);
        $interceptor->setStub($client);
        $this->assertEquals('\Grphp\Test\GetThingResp', $interceptor->getExpectedResponseMessageClass());
    }

    // When then stub is not set for some wild reason
    public function testGetExpectedResponseMessageClassWhenNoStub(): void
    {
        $this->expectException(StubNotFoundException::class);
        $method = 'getThing';
        $interceptor = new TestInterceptor();
        $interceptor->setMethod($method);
        $interceptor->getExpectedResponseMessageClass();
    }

    // When there is no matching response message
    public function testGetExpectedResponseMessageClassWhenNoResponseMessageFound(): void
    {
        $this->expectException(ResponseMessageLookupFailedException::class);

        $client = new ThingsClient('127.0.0.1:9000', [ 'credentials' => null ]);
        $method = 'fakeGetThing';

        $interceptor = new TestInterceptor();
        $interceptor->setMethod($method);
        $interceptor->setStub($client);
        $interceptor->getExpectedResponseMessageClass();
    }

    // Get the full service name
    public function testGetFullyQualifiedMethodName(): void
    {
        $requestMessage = new GetThingReq();
        $requestMessage->setId(1);
        $client = new ThingsClient('127.0.0.1:9000', [ 'credentials' => null ]);
        $method = 'getThing';

        $interceptor = new TestInterceptor();
        $interceptor->setMethod($method);
        $interceptor->setStub($client);
        $this->assertEquals('grphp.test.Things/GetThing', $interceptor->getFullyQualifiedMethodName());
    }

    // When then stub is not set for some wild reason
    public function testGetFullyQualifiedMethodNameWhenNoStub(): void
    {
        $this->expectException(StubNotFoundException::class);
        $method = 'getThing';
        $interceptor = new TestInterceptor();
        $interceptor->setMethod($method);
        $interceptor->getExpectedResponseMessageClass();
    }
}
