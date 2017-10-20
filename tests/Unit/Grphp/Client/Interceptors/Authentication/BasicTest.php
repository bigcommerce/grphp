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

namespace Grphp\Authentication;

use Grphp\Client\Interceptors\Authentication\Basic;
use Grphp\Client\Interceptors\Base;
use Grphp\Test\BaseTest;

final class BasicTest extends BaseTest
{
    /**
     * @param Base $interceptor
     * @return \Grphp\Client\Response
     */
    private function callInterceptor(Base $interceptor)
    {
        $resp = $this->buildResponse();
        return $interceptor->call(function() use (&$resp) {
            return $resp;
        });
    }

    public function testWithPassword()
    {
        $password = 'abcd';
        $interceptor = new Basic([
            'password' => $password
        ]);
        $this->callInterceptor($interceptor);
        $interceptorMetadata = $interceptor->getMetadata();
        $this->assertEquals($this->encode($password), $interceptorMetadata['authorization'][0]);
    }

    public function testWithNoOptions()
    {
        $interceptor = new Basic();
        $this->callInterceptor($interceptor);
        $this->assertArrayNotHasKey('authorization', $interceptor->getMetadata());
    }

    public function testWithUsernameAndPassword()
    {
        $username = 'abcd';
        $password = '1234';
        $interceptor = new Basic([
            'username' => $username,
            'password' => $password
        ]);
        $this->callInterceptor($interceptor);
        $interceptorMetadata = $interceptor->getMetadata();
        $this->assertEquals($this->encode("$username:$password"), $interceptorMetadata['authorization'][0]);
    }

    public function testWithCustomMetadataKey()
    {
        $metadataKey = 'foo';
        $password = 'abcd';
        $interceptor = new Basic([
            'password' => $password,
            'metadata_key' => $metadataKey
        ]);
        $this->callInterceptor($interceptor);
        $interceptorMetadata = $interceptor->getMetadata();
        $this->assertEquals($this->encode($password), $interceptorMetadata[$metadataKey][0]);
    }

    /**]
     * @param string $str
     * @return string
     */
    private function encode(string $str)
    {
        $s = base64_encode($str);
        return "Basic $s";
    }

}
