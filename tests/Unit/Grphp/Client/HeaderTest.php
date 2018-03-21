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
namespace Grphp\Client;

use PHPUnit\Framework\TestCase;

final class HeaderTest extends TestCase
{

    public function testGetName()
    {
        $header = new Header('Foo');
        static::assertEquals('foo', $header->getName());
    }

    public function testAddValue()
    {
        $header = new Header('foo');
        static::assertEquals($header, $header->addValue('bar'));
        static::assertEquals(['bar'], $header->getValues());
    }

    public function testAddAnotherValue()
    {
        $header = new Header('foo');
        $header->addValue('bar');
        $header->addValue('baz');
        static::assertEquals(['bar', 'baz'], $header->getValues());
    }

    public function testGetValues()
    {
        $header = new Header('foo');
        static::assertEquals([], $header->getValues());
        $header->addValue('bar');
        static::assertEquals(['bar'], $header->getValues());
        $header->addValue('baz');
        static::assertEquals(['bar', 'baz'], $header->getValues());
    }

    public function testGetValuesAsString()
    {
        $header = new Header('foo');
        static::assertEquals('', $header->getValuesAsString());
        $header->addValue('bar');
        static::assertEquals('bar', $header->getValuesAsString());
        $header->addValue('baz');
        static::assertEquals('bar,baz', $header->getValuesAsString());
    }

    public function testGetFirstValue()
    {
        $header = new Header('foo');
        $header->addValue('bar');
        $header->addValue('baz');
        static::assertEquals('bar', $header->getFirstValue());
    }

    public function testGetFirstValueNonExistent()
    {
        $header = new Header('foo');
        static::assertEquals('', $header->getFirstValue());
    }

    public function testGetFirstValueWithDefault()
    {
        $header = new Header('foo');
        static::assertEquals('yak', $header->getFirstValue('yak'));
    }
}
