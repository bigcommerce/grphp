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
use Grphp\Client\Request as RequestContext;
use Grphp\Protobuf\SerializationException;
use Grphp\Protobuf\Serializer;

/**
 * Build HTTP requests for the nghttpx h2 proxy
 */
class RequestFactory
{
    /** @var Config */
    private $config;
    /** @var Serializer */
    private $serializer;

    /**
     * @param Config $config
     * @param Serializer $serializer
     */
    public function __construct(Config $config, Serializer $serializer)
    {
        $this->config = $config;
        $this->serializer = $serializer;
    }

    /**
     * @param RequestContext $requestContext
     * @return Request
     * @throws SerializationException
     */
    public function build(RequestContext $requestContext): Request
    {
        $url = trim($this->config->getBaseUri(), '/') . '/' . $requestContext->getPath();
        $message = $this->serializer->serializeRequest($requestContext);
        $headers = $this->buildHeaders($requestContext);

        return new Request(
            $url,
            $message,
            $headers,
            $this->config->getProxyUri(),
            $requestContext->getTimeout()
        );
    }

    /**
     * @param RequestContext $requestContext
     * @return HeaderCollection
     */
    private function buildHeaders(RequestContext $requestContext): HeaderCollection
    {
        $headers = new HeaderCollection();

        $metadata = $requestContext->getMetadata();
        foreach ($metadata as $k => $v) {
            if (is_string($v)) {
                $headers->add($k, $v);
            } else {
                foreach ($v as $v2) {
                    $headers->add($k, $v2);
                }
            }
        }
        $deadline = $requestContext->buildDeadline();
        if (!empty($deadline)) {
            $headers->add('Deadline', strval($deadline));
        }
        $headers->add('Upgrade', 'h2c');
        $headers->add('Connection', 'Upgrade');
        $headers->add('Content-Type', $this->config->getContentType());
        $headers->add('TE', 'trailers');
        $headers->add('User-Agent', 'grphp/1.0.0');
        $headers->add('Grpc-Encoding', 'identity');
        return $headers;
    }
}
