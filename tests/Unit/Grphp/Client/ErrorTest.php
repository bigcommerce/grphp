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

final class ErrorTest extends TestCase
{
    /** @var Error */
    protected $error;
    /** @var \stdClass */
    protected $status;
    /** @var float */
    protected $elapsed;

    /** @var \Grphp\Client */
    private $client;
    /** @var \Grphp\Client\Config */
    private $clientConfig;

    private function buildClient(array $options = [])
    {
        $options = array_merge([
            'hostname' => '0.0.0.0:9000',
        ], $options);

        $this->clientConfig = new \Grphp\Client\Config($options);
        $this->client = new \Grphp\Client(\Grphp\Test\ThingsClient::class, $this->clientConfig);
    }

    public function setUp()
    {
        $this->buildClient([
            'error_serializer' => \Grphp\Serializers\Errors\Json::class,
        ]);
        $this->elapsed = rand(0.0, 20.0);

        $headerRegistry = new HeaderCollection();
        $headerRegistry->add(Error::ERROR_METADATA_KEY, '{"message": "Test"}');
        $this->status = new Error\Status(0, 'OK', $headerRegistry);;
    }

    /**
     * Test the pass-through to the exception class
     */
    public function testConstructor()
    {
        $error = new Error($this->clientConfig, $this->status);
        static::assertInstanceOf(\Grphp\Client\Error::class, $error);
        static::assertEquals("Error: {$this->status->getDetails()}", $error->getMessage());
        static::assertEquals($this->status->getCode(), $error->getCode());
    }

    public function testGetDetails()
    {
        $error = new Error($this->clientConfig, $this->status);
        static::assertEquals($this->status->getDetails(), $error->getDetails());
    }

    public function testGetStatusCode()
    {
        $error = new Error($this->clientConfig, $this->status);
        static::assertEquals($this->status->getCode(), $error->getStatusCode());
    }

    public function testGetStatusMetadata()
    {
        $error = new Error($this->clientConfig, $this->status);
        static::assertEquals($this->status->getHeaders()->toArray(), $error->getStatusMetadata());
    }

    public function testGetTrailer()
    {
        $error = new Error($this->clientConfig, $this->status);
        static::assertEquals(['message' => 'Test'], $error->getTrailer());
    }

    public function testGetConfig()
    {
        $error = new Error($this->clientConfig, $this->status);
        static::assertEquals($this->clientConfig, $error->getConfig());
    }
}
