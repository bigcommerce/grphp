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
use stdClass;

/**
 * Abstracts a gRPC response to provide accessor information into metadata, status codes,
 * trailing output metadata, and more.
 *
 * @package Grphp\Client
 */
class Response
{
    /** @var \Google\Protobuf\Internal\Message $response */
    protected $response;
    /** @var stdClass $status */
    protected $status;
    /** @var float $elapsed */
    protected $elapsed = 0.0;

    /**
     * @param Message $response
     * @param stdClass $status
     */
    public function __construct(Message $response, $status)
    {
        $this->response = $response;
        $this->status = $status;
    }

    /**
     * @return Message
     */
    public function getResponse(): Message
    {
        return $this->response;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status->code;
    }

    /**
     * @return string
     */
    public function getStatusDetails(): string
    {
        return $this->status->details;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->status->metadata;
    }

    /**
     * @return stdClass
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return float
     */
    public function getElapsed(): float
    {
        return $this->elapsed;
    }

    /**
     * @param float $time
     * @return void
     */
    public function setElapsed(float $time)
    {
        $this->elapsed = $time;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->getStatusCode() == 0;
    }

    /**
     * @param array $newMetadata
     * @param bool $merge
     * @return void
     */
    public function setMetadata(array $newMetadata = [], bool $merge = true)
    {
        if ($merge) {
            $this->status->metadata = array_merge($this->status->metadata, $newMetadata);
        } else {
            $this->status->metadata = $newMetadata;
        }
    }

    /**
     * @return float
     */
    public function getInternalExecutionTime(): float
    {
        return array_key_exists('timer', $this->status->metadata)
          && count($this->status->metadata['timer']) > 0
            ? floatval($this->status->metadata['timer'][0])
            : 0.0;
    }
}
