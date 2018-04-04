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

namespace Grphp\Client\Strategy\Grpc;

use Grphp\Client\Error;
use Grphp\Client\HeaderCollection;
use Grphp\Client\Request;
use Grphp\Client\Response;
use Grphp\Client\Strategy\StrategyInterface;

/**
 * Execute the gRPC strategy, which utilizes the underlying gRPC core libraries, for making the client request
 */
class Strategy implements StrategyInterface
{
    /** @var Config */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Execute an outbound gRPC request
     *
     * @param Request $request
     * @return Response
     * @throws Error
     */
    public function execute(Request $request): Response
    {
        $method = $request->getMethod();
        list($resp, $grpcStatus) = $request->getClient()->$method(
            $request->getMessage(),
            $request->getMetadata(),
            $request->getOptions()
        )->wait();

        $headers = new HeaderCollection();

        if (is_null($grpcStatus)) { // in the very bad case even this fails to return
            $status = new Error\Status(\Grpc\STATUS_UNKNOWN, '', $headers);
        } else {
            $metadata = $grpcStatus->metadata;
            foreach ($metadata as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $v2) {
                        $headers->add($k, $v2);
                    }
                } else {
                    $headers->add($k, $v);
                }
            }
            $status = new Error\Status(intval($grpcStatus->code), strval($grpcStatus->details), $headers);
        }

        if (is_null($resp)) {
            $request->fail($status);
        }

        return $request->succeed($resp, $status);
    }
}
