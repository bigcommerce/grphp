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

namespace Grphp\Client\Strategy\Envoy;

use Grphp\Client\HeaderCollection;

/**
 * Represents a request being sent through Envoy by grphp
 */
class Request
{
    /** @var string */
    private string $url;
    /** @var string */
    private string $message;
    /** @var HeaderCollection */
    private HeaderCollection $headers;
    /** @var float|null */
    private ?float $timeout;
    /** @var bool $retries */
    private bool $retries;
    /** @var int $retriesMax */
    private int $retriesMax;
    /** @var int[] $retriesStatusCodes */
    private array $retriesStatusCodes;

    /**
     * @param string $url The URL Envoy runs on
     * @param string $message The binary serialized protobuf message
     * @param HeaderCollection $headers Headers to send
     * @param bool $retries Whether to enable retries for this request
     * @param int $retriesMax Maximum number of retries
     * @param int[] $retriesStatusCodes The retryable gRPC status codes
     * @param float|null $timeout
     */
    public function __construct(
        string $url,
        string $message,
        HeaderCollection $headers,
        bool $retries,
        int $retriesMax,
        array $retriesStatusCodes,
        ?float $timeout = null
    ) {
        $this->url = $url;
        $this->message = $message;
        $this->headers = $headers;
        $this->retries = $retries;
        $this->retriesMax = $retriesMax;
        $this->retriesStatusCodes = $retriesStatusCodes;
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return HeaderCollection
     */
    public function getHeaders(): HeaderCollection
    {
        return $this->headers;
    }

    /**
     * @return float|null
     */
    public function getTimeout(): ?float
    {
        return $this->timeout;
    }

    /**
     * @return bool
     */
    public function areRetriesEnabled(): bool
    {
        return $this->retries;
    }

    /**
     * @return int
     */
    public function getMaxRetries(): int
    {
        return $this->retriesMax;
    }

    /**
     * @return int[]
     */
    public function getRetryableStatusCodes(): array
    {
        return $this->retriesStatusCodes;
    }
}
