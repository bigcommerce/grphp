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

use Grphp\Client\Error\Status;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    /** @var \Grphp\Test\GetThingResp */
    protected $resp;
    /** @var \Grphp\Client\Response */
    protected $response;
    /** @var Status */
    protected $statusObj;
    /** @var float */
    protected $elapsed;

    protected function setUp(): void
    {
        parent::setUp();

        $thing = new \Grphp\Test\Thing();
        $thing->setId(1234);
        $thing->setName('Test');
        $this->resp = new \Grphp\Test\GetThingResp();
        $this->resp->setThing($thing);

        $responseHeaders = new HeaderCollection();
        $responseHeaders->add('timer', rand(0.0, 200.0));
        $this->statusObj = new Status(0, 'OK', $responseHeaders);

        $this->elapsed = rand(0.0, 200.0);
        $this->response = new Response($this->resp, $this->statusObj);
        $this->response->setElapsed($this->elapsed);
    }

    public function testGetResponse()
    {
        static::assertEquals($this->resp, $this->response->getResponse());
    }

    public function testGetStatusCode()
    {
        static::assertEquals($this->statusObj->getCode(), $this->response->getStatusCode());
    }

    public function testGetStatusDetails()
    {
        static::assertEquals($this->statusObj->getDetails(), $this->response->getStatusDetails());
    }

    public function testGetMetadata()
    {
        static::assertEquals($this->statusObj->getHeaders()->toArray(), $this->response->getMetadata());
    }

    public function testGetStatus()
    {
        static::assertEquals($this->statusObj, $this->response->getStatus());
    }

    public function testGetElapsed()
    {
        static::assertEquals($this->elapsed, $this->response->getElapsed());
    }

    public function testGetInternalExecutionTime()
    {
        $timer = $this->statusObj->getHeaders()->get('timer')->getFirstValue();
        static::assertEquals($timer, $this->response->getInternalExecutionTime());
    }
}
