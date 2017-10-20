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

namespace Grphp\Test;

use Grphp\Client;
use Grphp\Client\Config;
use Grphp\Client\Response;
use PHPUnit\Framework\TestCase;
use \Grphp\Serializers\Errors\Json as JsonErrorSerializer;

class BaseTest extends TestCase
{
    /** @var Config $clientConfig */
    protected $clientConfig;
    /** @var Client $client */
    protected $client;
    /**
     * @param array $options
     * @return Client
     */
    protected function buildClient(array $options = []): Client
    {
        $options = array_merge([
            'hostname' => '0.0.0.0:9000',
            'error_serializer' => new JsonErrorSerializer()
        ], $options);

        $this->clientConfig = new Config();
        $this->clientConfig->setHostname($options['hostname']);
        $this->clientConfig->setErrorSerializer($options['error_serializer']);
        $this->client = new Client(ThingsClient::class, $this->clientConfig);
        return $this->client;
    }

    protected function buildResponse()
    {
        $status = new \stdClass();
        $status->code = 0;
        $status->details = 'foo';
        $status->code = 0;
        $status->details = 'OK';
        $status->metadata = [
            'error-internal-bin' => ['{"message": "Test"}'],
        ];
        return new Response(new GetThingResp(), $status);
    }
}
