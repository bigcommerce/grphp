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

namespace Grphp\Client\Interceptors\LinkerD;

use Grphp\Client\Error\Status;
use Grphp\Client\HeaderCollection;
use Grphp\Client\Response;
use Grphp\Test\GetThingResp;
use PHPUnit\Framework\TestCase;
use Grphp\Client\Interceptors\Base;

final class ContextPropagationTest extends TestCase
{
    /** @var ContextPropagation $interceptor */
    protected $interceptor;

    public function setUp()
    {
        $this->interceptor = new ContextPropagation();
    }

    /**
     * @param string $incomingKey
     * @param string $metadataKey
     * @param string $value
     * @dataProvider providerMetadataInServer
     */
    public function testMetadataInServer($incomingKey, $metadataKey, $value)
    {
        $_SERVER[$incomingKey] = $value;
        $this->callInterceptor($this->interceptor);
        $interceptorMetadata = $this->interceptor->getMetadata();
        $this->assertEquals($value, $interceptorMetadata[$metadataKey][0]);
    }
    public function providerMetadataInServer()
    {
        $data = [];
        foreach (ContextPropagation::METADATA_KEYS as $k => $v) {
            $data[] = [$k, $v, 'foo'];
        }
        return $data;
    }

    /**
     * @param string $incomingKey
     * @param string $metadataKey
     * @param string $value
     * @dataProvider providerMetadataInRequest
     */
    public function testMetadataInRequest($incomingKey, $metadataKey, $value)
    {
        $_REQUEST[$incomingKey] = $value;
        $this->callInterceptor($this->interceptor);
        $interceptorMetadata = $this->interceptor->getMetadata();
        $this->assertEquals($value, $interceptorMetadata[$metadataKey][0]);
    }
    public function providerMetadataInRequest()
    {
        $data = [];
        foreach (ContextPropagation::METADATA_KEYS as $k => $v) {
            $data[] = [$k, $v, 'foo'];
        }
        return $data;
    }

    /**
     * @param string $incomingKey
     * @param string $metadataKey
     * @param string $serverValue
     * @param string $requestValue
     * @dataProvider providerMetadataPrecedence
     */
    public function testMetadataPrecedence($incomingKey, $metadataKey, $serverValue, $requestValue)
    {
        $_SERVER[$incomingKey] = $serverValue;
        $_REQUEST[$incomingKey] = $requestValue;
        $this->callInterceptor($this->interceptor);
        $interceptorMetadata = $this->interceptor->getMetadata();
        $this->assertEquals($requestValue, $interceptorMetadata[$metadataKey][0]);
    }
    public function providerMetadataPrecedence()
    {
        $data = [];
        foreach (ContextPropagation::METADATA_KEYS as $k => $v) {
            $data[] = [$k, $v, 'foo', 'bar'];
        }
        return $data;
    }

    /**
     * @param Base $interceptor
     * @return Response
     */
    private function callInterceptor(Base $interceptor): Response
    {
        $resp = $this->buildResponse();
        return $interceptor->call(function () use (&$resp) {
            return $resp;
        });
    }

    /**
     * @return Response
     */
    private function buildResponse(): Response
    {
        $headers = new HeaderCollection();
        $headers->add('error-internal-bin', '{"message": "Test"}');
        $status = new Status(0, 'OK', $headers);
        return new Response(new GetThingResp(), $status);
    }
}
