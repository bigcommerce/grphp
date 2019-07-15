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

namespace Grphp\Client\Strategy\H2Proxy;

use Exception;
use Grphp\Client\Error\Status;
use Grphp\Client\HeaderCollection;
use Throwable;

/**
 * Represents a failed request that stores the body response, headers, and error message
 */
class RequestException extends Exception
{
    /** @var string */
    protected $body;
    /** @var HeaderCollection */
    protected $headers;

    /**
     * @param string $body
     * @param HeaderCollection $headers
     */
    public function __construct(string $body, HeaderCollection $headers, Throwable $previous = null)
    {
        $this->body = $body;
        $this->headers = $headers;

        parent::__construct($this->getErrorMessage(), $this->getStatusCode(), $previous);
    }

    /**
     * Get the gRPC status code for this request
     *
     * @return integer
     */
    private function getStatusCode(): int
    {
        $header = $this->headers->get('grpc-status');
        return $header ? (int)$header->getFirstValue() : Status::CODE_UNKNOWN;
    }

    /**
     * Get the gRPC error message for this failed request
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        $header = $this->headers->get('grpc-message');
        return $header ? $header->getFirstValue() : substr($this->body, 0, 255);
    }

    /**
     * Return the headers sent in this failed request
     *
     * @return HeaderCollection
     */
    public function getHeaders(): HeaderCollection
    {
        return $this->headers;
    }
}
