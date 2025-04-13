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

namespace Grphp\Client\Error;

use Grphp\Client\HeaderCollection;
use InvalidArgumentException;

/**
 * Represents an error status response from a gRPC (or proxied) call
 */
class Status
{
    const CODE_OK = 0;
    const CODE_CANCELLED = 1;
    const CODE_UNKNOWN = 2;
    const CODE_INVALID_ARGUMENT = 3;
    const CODE_DEADLINE_EXCEEDED = 4;
    const CODE_NOT_FOUND = 5;
    const CODE_ALREADY_EXISTS = 6;
    const CODE_PERMISSION_DENIED = 7;
    const CODE_RESOURCE_EXHAUSTED = 8;
    const CODE_FAILED_PRECONDITION = 9;
    const CODE_ABORTED = 10;
    const CODE_OUT_OF_RANGE = 11;
    const CODE_UNIMPLEMENTED = 12;
    const CODE_INTERNAL = 13;
    const CODE_UNAVAILABLE = 14;
    const CODE_DATA_LOSS = 15;
    const CODE_UNAUTHENTICATED = 16;

    private const CODE_STRING_MAP = [
        self::CODE_OK => 'OK',
        self::CODE_CANCELLED => 'CANCELLED',
        self::CODE_UNKNOWN => 'UNKNOWN',
        self::CODE_INVALID_ARGUMENT => 'INVALID_ARGUMENT',
        self::CODE_DEADLINE_EXCEEDED => 'DEADLINE_EXCEEDED',
        self::CODE_NOT_FOUND => 'NOT_FOUND',
        self::CODE_ALREADY_EXISTS => 'ALREADY_EXISTS',
        self::CODE_PERMISSION_DENIED => 'PERMISSION_DENIED',
        self::CODE_RESOURCE_EXHAUSTED => 'RESOURCE_EXHAUSTED',
        self::CODE_FAILED_PRECONDITION => 'FAILED_PRECONDITION',
        self::CODE_ABORTED => 'ABORTED',
        self::CODE_OUT_OF_RANGE => 'OUT_OF_RANGE',
        self::CODE_UNIMPLEMENTED => 'UNIMPLEMENTED',
        self::CODE_INTERNAL => 'INTERNAL',
        self::CODE_UNAVAILABLE => 'UNAVAILABLE',
        self::CODE_DATA_LOSS => 'DATA_LOSS',
        self::CODE_UNAUTHENTICATED => 'UNAUTHENTICATED',
    ];

    /**
     * Returns the descriptive string for a given status code number.
     *
     * @param int $number
     *
     * @return string
     */
    public static function getStatusCodeString(int $number): string
    {
        if (!isset(self::CODE_STRING_MAP[$number])) {
            throw new InvalidArgumentException("Invalid gRPC status code number: $number");
        }

        return self::CODE_STRING_MAP[$number];
    }

    /** @var int */
    private $code;
    /** @var string */
    private $details;
    /** @var HeaderCollection */
    private $headers;

    /**
     * @param int $code
     * @param string $details
     * @param ?HeaderCollection $headers
     */
    public function __construct(int $code, string $details, ?HeaderCollection $headers = null)
    {
        $this->code = $code;
        $this->details = $details;
        $this->headers = $headers ?? (new HeaderCollection());
    }

    /**
     * The gRPC status code that was returned
     *
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * The named gRPC status code string that was returned (e.g. "DEADLINE_EXCEEDED").
     *
     * @return string
     */
    public function getString(): string
    {
        return self::getStatusCodeString($this->code);
    }

    /**
     * The gRPC error details that were returned
     *
     * @return string
     */
    public function getDetails(): string
    {
        return $this->details;
    }

    /**
     * A registry of response headers
     *
     * @return HeaderCollection
     */
    public function getHeaders(): HeaderCollection
    {
        return $this->headers;
    }
}
