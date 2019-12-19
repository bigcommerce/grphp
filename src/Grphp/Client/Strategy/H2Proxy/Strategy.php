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

use Grphp\Client\ErrorStatus;
use Grphp\Client\Error\Status;
use Grphp\Client\Request as ClientRequest;
use Grphp\Client\Response as ClientResponse;
use Grphp\Client\Strategy\StrategyInterface;
use Grphp\Protobuf\Serializer;

/**
 * Strategy for serving requests over HTTP/1.1 via a nghttpx service that proxies the requests into gRPC. This sends
 * the binary protobuf to the service along with the metadata as HTTP headers, expecting a HTTP response with
 * the expected protobuf message back. Trailing metadata from the response is similarly in HTTP headers.
 */
class Strategy implements StrategyInterface
{
    /** @var Serializer */
    private $serializer;
    /** @var RequestFactory */
    private $requestFactory;
    /** @var RequestExecutor */
    private $requestExecutor;

    /**
     * @param Serializer $serializer
     * @param RequestFactory $requestFactory
     * @param RequestExecutor $requestExecutor
     */
    public function __construct(
        Serializer $serializer,
        RequestFactory $requestFactory,
        RequestExecutor $requestExecutor
    ) {
        $this->serializer = $serializer;
        $this->requestFactory = $requestFactory;
        $this->requestExecutor = $requestExecutor;
    }

    /**
     * Execute an outbound HTTP/1.1 request to nghttpx
     *
     * @param ClientRequest $clientRequest
     * @return ClientResponse
     * @throws \Grphp\Client\Error
     * @throws \Google\Protobuf\Internal\Exception
     * @throws \Grphp\Protobuf\SerializationException
     */
    public function execute(ClientRequest $clientRequest): ClientResponse
    {
        $response = null;
        $request = $this->requestFactory->build($clientRequest);
        try {
            $response = $this->requestExecutor->send($request);
        } catch (RequestException $e) {
            $message = "gRPC call `{$clientRequest->getPath()}` failed with `{$e->getMessage()}`";
            $clientRequest->fail(new Status($e->getCode(), $message, $e->getHeaders()));
        }

        return $this->handleSuccess($clientRequest, $response);
    }

    /**
     * @param ClientRequest $clientRequest
     * @param Response $response
     * @return ClientResponse
     * @throws \Google\Protobuf\Internal\Exception
     * @throws \Grphp\Protobuf\SerializationException
     */
    private function handleSuccess(ClientRequest $clientRequest, Response $response)
    {
        $responseMessage = $this->serializer->deserialize(
            $response->getBody(),
            $clientRequest->getExpectedResponseMessageClass()
        );

        $status = new Status(Status::CODE_OK, '', $response->getHeaders());
        return $clientRequest->succeed($responseMessage, $status);
    }
}
