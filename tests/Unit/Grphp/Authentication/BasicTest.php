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

use PHPUnit\Framework\TestCase;

final class BasicTest extends TestCase
{
    /**
     * @dataProvider providerGetMetadata
     * @param array $options
     */
    public function testGetMetadata(array $options = [], array $expected = [])
    {
        $auth = new Basic($options);
        $this->assertEquals($expected, $auth->getMetadata());
    }
    public function providerGetMetadata()
    {
        return [
            [ // no options
                [],
                [],
            ],
            [ // password only
                ['password' => 'foo'],
                ['authorization' => [$this->encode('foo')]],
            ],
            [ // username + password
                [
                    'username' => 'foo',
                    'password' => 'bar',
                ],
                ['authorization' => [$this->encode('foo:bar')]],
            ],
            [ // custom metadata key
                [
                    'username' => 'foo',
                    'password' => 'bar',
                    'metadata_key' => 'baz',
                ],
                ['baz' => [$this->encode('foo:bar')]],
            ]
        ];
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
