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

namespace Grphp\Client;

use Grphp\Client\Config;
use Grphp\Client\Error;
use \Grphp\Test\BaseTest;

final class ErrorTest extends BaseTest
{
    /** @var Error */
    protected $error;
    /** @var \stdClass */
    protected $status;
    /** @var float */
    protected $elapsed;

    public function setUp()
    {
        $this->buildClient();
        $status = new \stdClass();
        $status->code = 0;
        $status->details = 'OK';
        $status->metadata = [
            Error::ERROR_METADATA_KEY => ['{"message": "Test"}'],
        ];
        $this->status = $status;
        $this->error = new Error($this->clientConfig, $status);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(Error::class, $this->error);
        $this->assertEquals("Error: {$this->status->details}", $this->error->getMessage());
        $this->assertInstanceOf(Config::class, $this->error->getConfig());
        $this->assertEquals($this->status->code, $this->error->getCode());
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf(Config::class, $this->error->getConfig());
        $this->assertEquals($this->clientConfig, $this->error->getConfig());
    }

    public function testGetTrailer()
    {
        $this->assertEquals(['message' => 'Test'], $this->error->getTrailer());
    }
}
