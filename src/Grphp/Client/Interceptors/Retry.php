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
declare(strict_types=1);

namespace Grphp\Client\Interceptors;

use Closure;
use Grphp\Client\Error;
use Grphp\Client\Error\Status;
use Grphp\Client\Response;

class Retry extends Base
{
    private const DEFAULT_MAX_RETRIES = 3;
    private const DEFAULT_DELAY_MS = 200;

    private int $maxRetries;
    private int $delayMilliseconds;
    /** @var string[] */
    private array $retryOnStatuses;
    private Closure $backoffFunc;

    /**
     * @param array{
     *     max_retries: int,
     *     delay_milliseconds: int,
     *     retry_on_statuses: string[],
     *     backoff_func: Closure,
     * } $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->maxRetries = $options['max_retries'] ?? self::DEFAULT_MAX_RETRIES;
        $this->delayMilliseconds = $options['delay_milliseconds'] ?? self::DEFAULT_DELAY_MS;
        $this->retryOnStatuses = $options['retry_on_statuses'] ?? [Status::CODE_UNAVAILABLE];
        $this->backoffFunc = $options['backoff_func'] ?? function (int $attempt, int $delayMilliseconds) {
            usleep(pow(2, $attempt) * $delayMilliseconds * 1000);
        };
    }

    /**
     * @param callable $callback
     * @return Response
     * @throws Error
     */
    public function call(callable $callback): Response
    {
        return $this->attemptCall($callback, 0);
    }

    private function attemptCall(callable $callback, int $attempt): Response
    {
        try {
            return $callback();
        } catch (Error $e) {
            if ($this->maxRetries > $attempt && in_array($e->getStatusCode(), $this->retryOnStatuses)) {
                call_user_func_array($this->backoffFunc, [$attempt, $this->delayMilliseconds]);

                return $this->attemptCall($callback, $attempt + 1);
            }

            throw $e;
        }
    }
}
