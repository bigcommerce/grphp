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

use Grphp\Client\Error as ClientError;
use Grphp\Client\Error\Status;
use Grphp\Client\Response as ClientResponse;
use Grphp\Client\Request as ClientRequest;
use Grphp\Client\FailedResponseClassLookupException;
use Grphp\Client\Strategy\StrategyInterface;
use Grphp\Protobuf\SerializationException;
use Grphp\Protobuf\Serializer;

/**
 * Strategy for using Envoy for outbound gRPC requests
 */
class Strategy implements StrategyInterface
{
    /** @var RequestExecutor $requestExecutor */
    private $requestExecutor;
    /** @var RequestFactory $requestFactory */
    private $requestFactory;
    /** @var Serializer $serializer */
    private $serializer;

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
     * @param ClientRequest $request
     * @return ClientResponse
     * @throws FailedResponseClassLookupException
     * @throws SerializationException
     * @throws ClientError
     */
    public function execute(ClientRequest $request): ClientResponse
    {
        $response = null;
        $proxyRequest = $this->requestFactory->build($request);
        try {
            $response = $this->requestExecutor->send($proxyRequest);
            return $this->handleSuccess($request, $response);
        } catch (RequestException $e) {
            $message = "gRPC call `{$request->getPath()}` failed with `{$e->getMessage()}`";
            $request->fail(new Status($e->getCode(), $message, $e->getHeaders()));
        }
        return $this->handleSuccess($request, $response);
    }

    /**
     * @param ClientRequest $clientRequest
     * @param Response $response
     * @return ClientResponse
     * @throws FailedResponseClassLookupException
     * @throws SerializationException
     */
    private function handleSuccess(ClientRequest $clientRequest, Response $response): ClientResponse
    {
        $responseMessage = $this->serializer->deserialize(
            $response->getBody(),
            $clientRequest->getExpectedResponseMessageClass()
        );

        $status = new Status(Status::CODE_OK, '', $response->getHeaders());
        return $clientRequest->succeed($responseMessage, $status);
    }
}
