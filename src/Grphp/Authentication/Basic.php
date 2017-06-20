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
namespace Grphp\Authentication;

/**
 * Basic HTTP Auth adapters for gRPC requests
 *
 * @package Grphp\Authentication
 */
class Basic extends Base
{
    /**
     * @return array
     */
    public function getMetadata()
    {
        if (empty($this->options['password'])) {
            return [];
        } else {
            $username = trim($this->options['username']);
            $password = trim($this->options['password']);
            $username = empty($username) ? '' : "$username:";
            $authString = base64_encode("$username$password");
            return [
                $this->getAuthenticationMetadataKey() => [trim("Basic $authString")],
            ];
        }
    }

    /**
     * @return string
     */
    private function getAuthenticationMetadataKey()
    {
        return array_key_exists('metadata_key', $this->options) ?
            $this->options['metadata_key']
            : 'authorization';
    }
}
