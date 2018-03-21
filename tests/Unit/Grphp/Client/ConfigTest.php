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

use Grphp\Client\Config as ClientConfig;
use Grphp\Client\Strategy\Grpc\Strategy as GrpcStrategy;
use Grphp\Client\Strategy\H2Proxy\Config as H2ProxyConfig;
use Grphp\Client\Strategy\H2Proxy\StrategyFactory as H2ProxyStrategyFactory;
use PHPUnit\Framework\TestCase;
use Grphp\Serializers\Errors\Json as JsonErrorSerializer;

final class ConfigTest extends TestCase
{
    public function testDefaults()
    {
        $config = new ClientConfig();
        static::assertEquals('', $config->hostname);
        static::assertEquals(null, $config->authentication);
        static::assertEquals([], $config->authenticationOptions);
        static::assertEquals(JsonErrorSerializer::class, $config->errorSerializer);
        static::assertEquals([], $config->errorSerializerOptions);
        static::assertEquals('error-internal-bin', $config->errorMetadataKey);
        static::assertEquals([], $config->interceptorOptions);
        static::assertEquals(true, $config->useDefaultInterceptors);
        static::assertInstanceOf(GrpcStrategy::class, $config->getStrategy());
    }

    public function testSetters()
    {
        $config = new ClientConfig();
        $h2ProxyConfig = new H2ProxyConfig();
        $strategy = (new H2ProxyStrategyFactory($h2ProxyConfig))->build();
        $config->setStrategy($strategy);
        static::assertEquals($strategy, $config->getStrategy());
    }
}
