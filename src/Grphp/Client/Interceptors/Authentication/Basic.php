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

namespace Grphp\Client\Interceptors\Authentication;

use Grphp\Client\Interceptors\Base;
use Grphp\Client\Response;

/**
 * Adds Basic HTTP Auth adapters for gRPC requests
 *
 * @package Grphp\Client\Interceptors\Authentication
 */
class Basic extends Base
{
    /**
     * @param callable $callback
     * @return Response
     */
    public function call(callable $callback): Response
    {
        if ($this->passwordConfigured()) {
            $this->metadata[$this->getKey()] = $this->getValue();
        }
        return $callback();
    }

    /**
     * @return string[]
     */
    private function getValue(): array
    {
        $username = $this->getUsername();
        $password = $this->getPassword();
        $username = empty($username) ? '' : "$username:";
        $authString = base64_encode("$username$password");
        return [trim("Basic $authString")];
    }

    /**
     * @return string
     */
    private function getKey(): string
    {
        return $this->getOption('metadata_key', 'authorization');
    }

    /**
     * @return bool
     */
    private function passwordConfigured(): bool
    {
        return !empty($this->getPassword());
    }

    /**
     * @return string
     */
    private function getPassword(): string
    {
        return trim($this->getOption('password', ''));
    }

    /**
     * @return string
     */
    private function getUsername(): string
    {
        return trim($this->getOption('username', ''));
    }
}
