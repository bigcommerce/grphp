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

use Grphp\Client\HeaderCollection;
use Grphp\Client\Request as ClientRequest;
use Grphp\Protobuf\SerializationException;
use Grphp\Protobuf\Serializer;

/**
 * Build Envoy requests from grphp request objects
 */
class RequestFactory
{
    protected Config $config;
    protected Serializer $serializer;

    public function __construct(Config $config, Serializer $serializer)
    {
        $this->config = $config;
        $this->serializer = $serializer;
    }

    /**
     * @param ClientRequest $requestContext
     * @return Request
     * @throws SerializationException
     */
    public function build(ClientRequest $requestContext): Request
    {
        $url = $this->config->getAddress() . '/' . $requestContext->getPath();
        $message = $this->serializer->serializeRequest($requestContext);
        $headers = $this->buildHeaders($requestContext);

        return new Request(
            $url,
            $message,
            $headers,
            $requestContext->getTimeout() ?: $this->config->getTimeout()
        );
    }

    private function buildHeaders(ClientRequest $clientRequest): HeaderCollection
    {
        $headers = HeaderCollection::fromRequest($clientRequest);
        $headers->add('Content-Type', $this->config->getContentType());
        $headers->add('User-Agent', $this->config->getUserAgent());
        // @see https://github.com/grpc/grpc/blob/653ba62/doc/PROTOCOL-HTTP2.md?plain=1#L30
        $headers->add('TE', 'trailers');
        return $headers;
    }
}
