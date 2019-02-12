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

use Grphp\Client\HeaderCollection;

class Request
{
    /** @var string */
    private $proxyUri;
    /** @var string */
    private $url;
    /** @var string */
    private $message;
    /** @var HeaderCollection */
    private $headers;

    /**
     * @param string $url The URL of the nghttpx proxy
     * @param string $message The binary serialized protobuf message
     * @param HeaderCollection $headers Headers to send
     * @param string $proxyUri
     */
    public function __construct(string $url, string $message, HeaderCollection $headers, string $proxyUri = '')
    {
        $this->url = $url;
        $this->message = $message;
        $this->headers = $headers;
        $this->proxyUri = $proxyUri;
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
     * @return string
     */
    public function getProxyUri(): string
    {
        return $this->proxyUri;
    }
}
