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

namespace Grphp\Client;

use Grpc\BaseStub;

class RetryPoliciesFactory
{
    /**
     * Build a retry policy dynamically given a gRPC Client stub
     * @param BaseStub $client
     * @param Config $config
     * @return array
     */
    public function build(BaseStub $client, Config $config): array
    {
        // If we're on an old generated version and getServiceName or getExpectedResponseMessages does not exist,
        // we cannot dynamically build the retry policy, so skip
        if (!method_exists($client, 'getServiceName') || !method_exists($client, 'getExpectedResponseMessages'))
        {
            return [];
        }
        $serviceName = $client->getServiceName();

        $services = [];
        foreach ($client->getExpectedResponseMessages() as $methodName => $signature) {
            $services += [
                'service' => $serviceName,
                'method' => $methodName,
            ];
        }
        $methodConfiguration = [
            'name' => $services,
            'retryPolicy' => [
                'retryableStatusCodes' => $config->retriesStatusCodes,
                'maxAttempts' => $config->retriesMaxAttempts,
                'initialBackoff' => $config->retriesInitialBackoff,
                'backoffMultiplier' => $config->retriesBackoffMultiplier,
                'maxBackoff' => $config->retriesMaxBackoff
            ]
        ];
        return [
            'methodConfig' => $methodConfiguration,
        ];
    }
}
