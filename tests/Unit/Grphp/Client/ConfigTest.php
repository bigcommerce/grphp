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

use Grphp\Test\BaseTest;
use \Grphp\Serializers\Errors\Json as JsonErrorSerializer;
use Grphp\Test\TestSerializer;

final class ConfigTest extends BaseTest
{
    public function testDefaults()
    {
        $config = new Config();
        $this->assertEquals('', $config->getHostname());
        $this->assertInstanceOf(JsonErrorSerializer::class, $config->getErrorSerializer());
        $this->assertEquals(true, $config->useDefaultInterceptors());
        $this->assertEquals([], $config->getClientOptions());
    }

    public function testSetHostname()
    {
        $hostname = 'mesh:1234';
        $config = new Config();
        $config->setHostname($hostname);
        $this->assertEquals($hostname, $config->getHostname());
    }

    public function testSetErrorSerializer()
    {
        $serializer = new TestSerializer();
        $config = new Config();
        $config->setErrorSerializer($serializer);
        $this->assertEquals($serializer, $config->getErrorSerializer());
    }

    public function testSetUseDefaultInterceptors()
    {
        $config = new Config();
        $config->setUseDefaultInterceptors(false);
        $this->assertFalse($config->useDefaultInterceptors());
    }

    public function testSetClientOptions()
    {
        $opts = [
            'foo' => 'bar',
        ];
        $config = new Config();
        $config->setClientOptions($opts);
        $this->assertEquals($opts, $config->getClientOptions());
    }
}
