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
declare(strict_types=1);

namespace Grphp\Client\Strategy\Envoy;

/**
 * Configuration for the Envoy strategy
 */
class Config
{
    /** @var string The default hostname of the Envoy proxy */
    const DEFAULT_ENVOY_HOST = '127.0.0.1';
    /** @var int The default port of the Envoy proxy */
    const DEFAULT_ENVOY_PORT = 19001;
    /** @var int The default timeout for connecting to Envoy and resolving its result */
    const DEFAULT_TIMEOUT = 15;
    /** @var string The default content type to send on client requests */
    const DEFAULT_CONTENT_TYPE = 'application/grpc';
    /** @var string User agent to use when sending cURL requests to Envoy proxy */
    const DEFAULT_USER_AGENT = 'grphp/1.0.0';

    /** @var string */
    private $address;

    /** @var int */
    private $timeout;

    /** @var string */
    private $contentType;

    /** @var string */
    private $userAgent;

    /**
     * @param string|null $host The address of the Envoy proxy
     * @param int|null $port The port the Envoy proxy is receiving ingress on
     * @param int|null $timeout The timeout to connect to the proxy, in seconds
     * @param string|null $contentType The content type to use when issuing requests through this strategy
     * @param string|null $userAgent The user agent to use in egress requests to Envoy
     */
    public function __construct(
        string $host = self::DEFAULT_ENVOY_HOST,
        int $port = self::DEFAULT_ENVOY_PORT,
        int $timeout = self::DEFAULT_TIMEOUT,
        string $contentType = self::DEFAULT_CONTENT_TYPE,
        string $userAgent = self::DEFAULT_USER_AGENT
    ) {
        $this->address = $this->buildAddress($host, $port);
        $this->timeout = $timeout;
        $this->contentType = $contentType;
        $this->userAgent = $userAgent;
    }

    /**
     * @param string|null $host
     * @param int|null $port
     * @return string
     */
    private function buildAddress(string $host, int $port): string
    {
        $host = $host ?: static::DEFAULT_ENVOY_HOST;
        $port = $port ?: static::DEFAULT_ENVOY_PORT;
        return 'http://' . $host . ':' . $port;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }
}
