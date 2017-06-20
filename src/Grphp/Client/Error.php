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
namespace Grphp\Client;

use Grphp\Serializers\Errors\Base as BaseSerializer;

/**
 * Abstract a gRPC error, providing status codes, timings, and trailing metadata error
 * deserialization.
 *
 * @package Grphp\Client
 */
class Error extends \Exception
{
    /** @const string */
    const ERROR_METADATA_KEY = 'error-internal-bin';

    /** @var \stdClass $status */
    protected $status;
    /** @var Config $config */
    private $config;

    /**
     * @param \Grphp\Client\Config $config
     * @param \stdClass $status
     * @param float $elapsed
     */
    public function __construct(Config $config, $status)
    {
        $this->status = $status;
        $this->config = $config;
        parent::__construct("Error: $status->details", $status->code);
    }

    /**
     * @return mixed
     */
    public function getTrailer()
    {
        $trailer = null;
        $err = $this->getTrailingMetadataError();
        if ($err) {
            $serializer = $this->getErrorSerializer();
            if ($serializer) {
                $trailer = $serializer->deserialize($err);
            }
        }
        return $trailer;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return null|string
     */
    private function getTrailingMetadataError()
    {
        return $this->status
            && $this->status->metadata
            && array_key_exists(self::ERROR_METADATA_KEY, $this->status->metadata)
            && is_array($this->status->metadata[self::ERROR_METADATA_KEY])
                ? $this->status->metadata[self::ERROR_METADATA_KEY][0]
                : null;
    }

    /**
     * @return \Grphp\Serializers\Errors\Base
     */
    private function getErrorSerializer()
    {
        $class = $this->config->errorSerializer;
        if (is_object($class) && is_a($class, BaseSerializer::class)) {
            return $class;
        } elseif (is_string($class) && class_exists($class)) {
            return new $class($this->config->errorSerializerOptions);
        } else {
            return null;
        }
    }
}
