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

namespace Grpc;

const STATUS_INTERNAL = 13;
const STATUS_UNKNOWN = 2;
const STATUS_OK = 0;


if (!defined(\Grpc\ChannelCredentials::class)) {
    class ChannelCredentials
    {
        /**
         * Create insecure channel credentials
         *
         * @return null
         */
        public static function createInsecure()
        {
            return null;
        }

        /**
         * Set default roots pem.
         *
         * @param string $pem_roots PEM encoding of the server root certificates
         *
         * @return null
         * @throws \InvalidArgumentException
         */
        public static function setDefaultRootsPem($pem_roots)
        {
            return null;
        }
    }
}

if (!defined(\Grpc\Channel::class)) {
    class Channel
    {
    }
}

if (!defined(\Grpc\Timeval::class)) {
    class Timeval
    {
        public static function infFuture()
        {
            return null;
        }
    }
}

if (!defined(\Grpc\Call::class)) {
    class Call
    {
    }
}
