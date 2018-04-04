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

final class HeaderCollectionTest extends TestCase
{
    public function testAdd()
    {
        $headers = new HeaderCollection();
        $headers->add('foo', 'bar');

        $header = $headers->get('foo');
        static::assertNotNull($header);
        static::assertEquals('bar', $header->getValuesAsString());
    }

    public function testAddAnother()
    {
        $headers = new HeaderCollection();
        $headers->add('foo', 'bar');
        $headers->add('foo', 'baz');

        $header = $headers->get('foo');
        static::assertNotNull($header);
        static::assertEquals('bar,baz', $header->getValuesAsString());
    }

    public function testGetExisting()
    {
        $headers = new HeaderCollection();
        $headers->add('foo', 'bar');

        static::assertInstanceOf(Header::class, $headers->get('foo'));
        static::assertEquals('bar', $headers->get('foo')->getValuesAsString());
    }

    public function testNonExistent()
    {
        $headers = new HeaderCollection();
        static::assertNull($headers->get('foo'));
    }

    public function testToArray()
    {
        $headers = new HeaderCollection();
        $headers->add('foo', 'bar');
        static::assertEquals(['foo' => ['bar']], $headers->toArray());
    }

    public function testCompress()
    {
        $headers = new HeaderCollection();
        $headers->add('foo', 'bar');
        static::assertEquals(['foo: bar'], $headers->compress());
        $headers->add('foo', 'baz');
        static::assertEquals(['foo: bar,baz'], $headers->compress());
    }

    public function testCompressWithNoValues()
    {
        $headers = new HeaderCollection();
        static::assertEquals([], $headers->compress());
    }
}
