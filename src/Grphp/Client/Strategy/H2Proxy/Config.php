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

/**
 * Configuration for the h2proxy strategy
 */
class Config
{
    const DEFAULT_ADDRESS = '0.0.0.0:3000';
    /** @var int The default timeout for connecting to the nghttpx proxy and resolving its result */
    const DEFAULT_TIMEOUT = 15;

    /** @var string */
    private $baseUri;
    /** @var int */
    private $timeout;

    /**
     * @param string $baseUri The address and port of the nghttpx proxy
     * @param int $timeout The timeout to connect to the proxy, in seconds
     */
    public function __construct(string $baseUri = Config::DEFAULT_ADDRESS, int $timeout = Config::DEFAULT_TIMEOUT)
    {
        $this->baseUri = $baseUri;
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
