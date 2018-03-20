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

namespace Grphp\Client\Interceptors\LinkerD;

use Grphp\Client\Interceptors\Base;
use Grphp\Client\Response;

/**
 * Properly passes through l5d context propagation headers to enable dynamic l5d request routing
 *
 * @package Grphp\Client\Interceptors\LinkerD
 */
class ContextPropagation extends Base
{
    const METADATA_KEYS = [
        'HTTP_L5D_DTAB' => 'l5d-dtab',
        'HTTP_L5D_CTX_DTAB' => 'l5d-ctx-dtab',
        'HTTP_L5D_CTX_DEADLINE' => 'l5d-ctx-deadline',
        'HTTP_L5D_CTX_TRACE' => 'l5d-ctx-trace'
    ];

    /**
     * gRPC requires metadata keys to be as a string. Also, the PHP gRPC library expects them in an array.
     *
     * @param callable $callback
     * @return Response
     */
    public function call(callable $callback): Response
    {
        foreach (self::METADATA_KEYS as $incomingKey => $metadataKey) {
            if (array_key_exists($incomingKey, $_SERVER)) {
                $this->metadata[$metadataKey] = ['' . $_SERVER[$incomingKey]];
            }
            if (array_key_exists($incomingKey, $_REQUEST)) {
                $this->metadata[$metadataKey] = ['' . $_REQUEST[$incomingKey]];
            }
        }
        $response = $callback();
        return $response;
    }
}
