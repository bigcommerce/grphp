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

namespace Unit\Grphp\Client\Interceptors;

use Grphp\Client\Config;
use Grphp\Client\Error;
use Grphp\Client\Interceptors\Retry;
use Grphp\Client\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class RetryTest extends TestCase
{
    use ProphecyTrait;

    private Retry $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Retry([
            'max_retries' => 3,
            'delay_milliseconds' => 1,
        ]);
    }

    public function testCallSuccessAfterRetries(): void
    {
        $callback = function () {
            static $attempts = 0;
            $attempts++;
            if ($attempts < 2) {
                throw new Error(
                    new Config(),
                    new Error\Status(14, 'test')
                );
            }

            return $this->prophesize(Response::class)->reveal();
        };

        $this->assertInstanceOf(Response::class, $this->subject->call($callback));
    }

    public function testCallFailureAfterRetries(): void
    {
        $callback = function () {
            throw new Error(new Config(), new Error\Status(14, 'test'));
        };

        $this->expectException(Error::class);
        $this->subject->call($callback);
    }

    public function testCallFailureOnNonRetriableError(): void
    {
        $callback = function () {
            throw new Error(new Config(), new Error\Status(0, 'non-retriable error'));
        };

        $this->expectException(Error::class);
        $this->subject->call($callback);
    }

    public function testCallWithCustomBackoffFunc(): void
    {
        $callback = function () {
            static $attempts = 0;
            if ($attempts < 2) {
                $attempts++;
                throw new Error(
                    new Config(),
                    new Error\Status(14, 'test')
                );
            }

            return $this->prophesize(Response::class)->reveal();
        };

        $backoffCalled = 0;

        $this->subject = new Retry([
            'max_retries' => 3,
            'delay_milliseconds' => 1,
            'backoff_func' => function (int $attempt, int $delayMilliseconds) use (&$backoffCalled) {
                $this->assertSame(1, $delayMilliseconds);
                $backoffCalled++;
            },
        ]);

        $this->assertInstanceOf(Response::class, $this->subject->call($callback));
        $this->assertSame(2, $backoffCalled);
    }
}
