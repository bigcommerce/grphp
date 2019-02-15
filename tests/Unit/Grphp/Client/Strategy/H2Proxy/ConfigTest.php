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
namespace Grphp\Client\Strategy\H2Proxy;

use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    /**
     * @param string $baseUri
     * @param int $timeout
     * @dataProvider providerCustom
     */
    public function testCustom(string $baseUri, int $timeout)
    {
        $config = new Config($baseUri, $timeout);
        static::assertEquals($baseUri, $config->getBaseUri());
        static::assertEquals($timeout, $config->getTimeout());
    }
    public function providerCustom()
    {
        return [
            ['localhost:1234', 5],
            ['my.service:5000', 0]
        ];
    }

    public function testDefaults()
    {
        $config = new Config();
        static::assertEquals(Config::DEFAULT_ADDRESS, $config->getBaseUri());
        static::assertEquals(Config::DEFAULT_TIMEOUT, $config->getTimeout());
        static::assertEquals('', $config->getProxyUri());
    }
}
