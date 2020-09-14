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

use Google\Protobuf\Internal\Message;
use Grpc\BaseStub;
use Grphp\Client\Error\Status;

/**
 * Outbound request object for a client-initiated request
 */
class Request
{
    const DEFAULT_TIMEOUT = 15.0; // Set a default timeout of 15 seconds

    /** @var string */
    private $method;
    /** @var Message */
    private $message;
    /** @var BaseStub */
    private $client;
    /** @var array */
    private $metadata;
    /** @var array */
    private $options;
    /** @var Config */
    private $config;

    /**
     * @param string $method
     * @param Message $message
     * @param BaseStub $client
     * @param array $metadata
     * @param array $options
     * @param Config $config
     */
    public function __construct(
        Config $config,
        string $method,
        $message,
        BaseStub $client,
        array $metadata = [],
        array $options = []
    ) {
        $this->method = $method;
        $this->message = $message;
        $this->client = $client;
        $this->metadata = $metadata;
        $this->options = $options;
        $this->config = $config;
    }

    /**
     * @param Status $status
     * @throws Error
     */
    public function fail(Status $status)
    {
        throw new Error($this->config, $status);
    }

    /**
     * @param Message $message
     * @param Status $status
     * @return Response
     */
    public function succeed($message, Status $status)
    {
        return new Response($message, $status);
    }

    /**
     * @return BaseStub
     */
    public function getClient(): BaseStub
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return string
     * @throws FailedResponseClassLookupException
     */
    public function getExpectedResponseMessageClass(): string
    {
        $method = lcfirst($this->method);
        $clientClass = get_class($this->client);
        if (!method_exists($this->client, 'getExpectedResponseMessages')) {
            throw new FailedResponseClassLookupException(
                "No getExpectedResponseMessages method found on the {$clientClass} client class"
            );
        }
        $clientResponses = $this->client->getExpectedResponseMessages();
        if (!array_key_exists($method, $clientResponses)) {
            throw new FailedResponseClassLookupException(
                "No response class mapping found for {$method} on the {$clientClass} client class"
            );
        }
        return $clientResponses[$method];
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return "{$this->getServiceName()}/{$this->getMethod()}";
    }

    /**
     * @return float
     */
    public function buildDeadline(): float
    {
        $options = $this->getOptions();
        $timeout = floatval($options['timeout'] ?? static::DEFAULT_TIMEOUT);
        return microtime(true) + $timeout;
    }

    /**
     * @return string
     */
    public function getServiceName(): string
    {
        $service = explode('\\', get_class($this->client));
        $clientName = array_pop($service);
        $clientName = preg_replace('/Client\z/', '', $clientName);
        return strtolower(implode('.', $service)) . '.' . $clientName;
    }

    /**
     * @return float|null
     */
    public function getTimeout(): ?float
    {
        return $this->options['timeout'] ?? null;
    }
}
