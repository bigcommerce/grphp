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

namespace Grphp\Client\Interceptors;

use Google\Protobuf\Internal\Message;
use Grpc\BaseStub;
use Grphp\Client\Response;

/**
 * Base interceptor class that can be extended to provide interception of client requests
 *
 * @package Grphp\Interceptors
 */
abstract class Base
{
    /** @var array */
    protected $options = [];
    /** @var Message */
    protected $request;
    /** @var string */
    protected $method;
    /** @var array */
    protected $metadata = [];
    /** @var BaseStub */
    protected $stub;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @param callable $callback
     * @return Response
     */
    abstract public function call(callable $callback);

    /**
     * @return Message
     */
    public function getRequest(): Message
    {
        return $this->request;
    }

    /**
     * @param Message $request
     * @return void
     */
    public function setRequest(&$request)
    {
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return void
     */
    public function setMethod(string &$method)
    {
        $this->method = $method;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     * @return void
     */
    public function setMetadata(array &$metadata = [])
    {
        $this->metadata = $metadata;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return void
     */
    public function setOptions(array &$options = [])
    {
        $this->options = $options;
    }

    /**
     * @param string $k
     * @param mixed $default
     * @return mixed
     */
    public function getOption(string $k, $default = null)
    {
        return array_key_exists($k, $this->options) ? $this->options[$k] : $default;
    }

    /**
     * @return BaseStub
     */
    public function getStub(): BaseStub
    {
        return $this->stub;
    }

    /**
     * @param $stub
     */
    public function setStub(BaseStub &$stub)
    {
        $this->stub = $stub;
    }
}
