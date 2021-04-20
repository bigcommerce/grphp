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
namespace Grphp\Client\Strategy\Envoy;

use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testConfigWithTimeout()
    {
        $host = 'localhost';
        $port = 1234;
        $timeout = 5;

        $config = new Config($host, $port, $timeout);

        $this->assertSame("http://$host:$port", $config->getAddress());
        $this->assertSame($timeout, $config->getTimeout());
    }

    public function testConfigWithNoTimeout()
    {
        $host = 'my.service';
        $port = 5000;
        $timeout = 0;

        $config = new Config($host, $port, $timeout);

        $this->assertSame("http://$host:$port", $config->getAddress());
        $this->assertSame(0, $config->getTimeout());
    }

    public function testConfigWithDefaults()
    {
        $config = new Config();
        $this->assertSame('http://' . Config::DEFAULT_ENVOY_HOST . ':' . Config::DEFAULT_ENVOY_PORT, $config->getAddress());
        $this->assertSame(Config::DEFAULT_TIMEOUT, $config->getTimeout());
        $this->assertSame(Config::DEFAULT_CONTENT_TYPE, $config->getContentType());
        $this->assertSame(Config::DEFAULT_USER_AGENT, $config->getUserAgent());
    }

    public function testConfigWithSetContentType()
    {
        $config = new Config(
            Config::DEFAULT_ENVOY_HOST,
            Config::DEFAULT_ENVOY_PORT,
            Config::DEFAULT_TIMEOUT,
            'application/grpc2'
        );
        $this->assertSame('application/grpc2', $config->getContentType());
    }

    public function testConfigWithSetUserAgent()
    {
        $config = new Config(
            Config::DEFAULT_ENVOY_HOST,
            Config::DEFAULT_ENVOY_PORT,
            Config::DEFAULT_TIMEOUT,
            Config::DEFAULT_USER_AGENT,
            'foobar:1.0.0'
        );
        $this->assertSame('foobar:1.0.0', $config->getUserAgent());
    }
}
