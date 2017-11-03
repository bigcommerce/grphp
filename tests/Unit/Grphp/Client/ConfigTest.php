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

use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testDefaults()
    {
        $config = new Config();
        $this->assertEquals('', $config->hostname);
        $this->assertEquals(null, $config->authentication);
        $this->assertEquals([], $config->authenticationOptions);
        $this->assertEquals(\Grphp\Serializers\Errors\Json::class, $config->errorSerializer);
        $this->assertEquals([], $config->errorSerializerOptions);
        $this->assertEquals('error-internal-bin', $config->errorMetadataKey);
        $this->assertEquals([], $config->interceptorOptions);
        $this->assertEquals(true, $config->useDefaultInterceptors);
    }
}
