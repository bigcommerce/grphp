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

use Grphp\Serializers\Errors\Base as BaseErrorSerializer;
use Grphp\Serializers\Errors\Json as JsonErrorSerializer;

/**
 * Configuration object for Grphp Client
 * @package Grphp\Client
 */
class Config
{
    /** @var string $hostname */
    private $hostname = '';
    /** @var BaseErrorSerializer $errorSerializer */
    private $errorSerializer;
    /** @var bool $useDefaultInterceptors */
    private $useDefaultInterceptors = true;
    /** @var array $clientOptions */
    private $clientOptions = [];

    /**
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * @param string $hostname
     * @return void
     */
    public function setHostname(string $hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * @return BaseErrorSerializer
     */
    public function getErrorSerializer(): BaseErrorSerializer
    {
        if (empty($this->errorSerializer)) {
            $this->errorSerializer = new JsonErrorSerializer();
        }
        return $this->errorSerializer;
    }

    /**
     * @param BaseErrorSerializer $serializer
     * @return void
     */
    public function setErrorSerializer(BaseErrorSerializer $serializer)
    {
        $this->errorSerializer = $serializer;
    }

    /**
     * @return bool
     */
    public function useDefaultInterceptors(): bool
    {
        return $this->useDefaultInterceptors;
    }

    /**
     * @param bool $use
     * @return void
     */
    public function setUseDefaultInterceptors(bool $use)
    {
        $this->useDefaultInterceptors = $use;
    }

    /**
     * @return array
     */
    public function getClientOptions(): array
    {
        return $this->clientOptions;
    }

    /**
     * @param array $options
     * @return void
     */
    public function setClientOptions(array $options)
    {
        $this->clientOptions = $options;
    }
}
