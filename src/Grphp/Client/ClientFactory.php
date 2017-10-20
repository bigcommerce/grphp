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
use Grpc\ChannelCredentials;

class ClientFactory
{
    /** @var string $className */
    private $className;
    /** @var string $hostname */
    private $hostname;
    /** @var Channel $channel */
    private $channel;
    /** @var array $options */
    private $options;

    /**
     * @param string $className Name of the client class to construct
     * @param string $hostname
     * @param Channel $channel
     * @param array $options
     */
    public function __construct(string $className, string $hostname, Channel $channel = null, array $options = [])
    {
        $this->className = $className;
        $this->hostname = $hostname;
        $this->channel = $channel;
        $this->options = $options;
    }

    /**
     * @return BaseStub
     */
    public function build(): BaseStub
    {
        if (!array_key_exists('credentials', $this->options)) {
            $this->options['credentials'] = ChannelCredentials::createInsecure();
        }
        $class = $this->className;
        return new $class($this->hostname, $this->options, $this->channel);
    }
}
