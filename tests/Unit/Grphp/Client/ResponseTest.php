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

use \Grphp\Test\BaseTest;

final class ResponseTest extends BaseTest
{
    /** @var \Grphp\Test\GetThingResp */
    protected $resp;
    /** @var \Grphp\Client\Response */
    protected $response;
    /** @var \stdClass */
    protected $statusObj;
    /** @var float */
    protected $elapsed;

    public function setUp()
    {
        $thing = new \Grphp\Test\Thing();
        $thing->setId(1234);
        $thing->setName('Test');
        $this->resp = new \Grphp\Test\GetThingResp();
        $this->resp->setThing($thing);

        $this->statusObj = new \stdClass();
        $this->statusObj->code = 0;
        $this->statusObj->details = 'OK';
        $this->statusObj->metadata = [
            'timer' => [rand(0.0, 200.0)],
        ];

        $this->elapsed = rand(0.0, 200.0);
        $this->response = new Response($this->resp, $this->statusObj);
        $this->response->setElapsed($this->elapsed);
    }

    public function testGetResponse()
    {
        $this->assertEquals($this->resp, $this->response->getResponse());
    }

    public function testGetStatusCode()
    {
        $this->assertEquals($this->statusObj->code, $this->response->getStatusCode());
    }

    public function testGetStatusDetails()
    {
        $this->assertEquals($this->statusObj->details, $this->response->getStatusDetails());
    }

    public function testGetMetadata()
    {
        $this->assertEquals($this->statusObj->metadata, $this->response->getMetadata());
    }

    public function testGetStatus()
    {
        $this->assertEquals($this->statusObj, $this->response->getStatus());
    }

    public function testGetElapsed()
    {
        $this->assertEquals($this->elapsed, $this->response->getElapsed());
    }

    public function testGetInternalExecutionTime()
    {
        $this->assertEquals($this->statusObj->metadata['timer'][0], $this->response->getInternalExecutionTime());
    }

    public function testIsSuccess()
    {
        $this->assertTrue($this->response->isSuccess());
    }

    public function testIsFailure()
    {
        $status = new \stdClass();
        $status->code = 42;
        $status->details = 'Oh noes';
        $status->metadata = [
            'timer' => [rand(0.0, 200.0)],
        ];

        $response = new Response($this->resp, $status);
        $this->assertFalse($response->isSuccess());
    }
}
