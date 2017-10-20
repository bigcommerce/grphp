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
namespace Grphp\Client\Interceptors;

use Grphp\Test\BaseTest;
use Grphp\Test\TestInterceptor;

final class RegistryTest extends BaseTest
{
    public function setUp()
    {
        parent::setUp();
        $this->buildClient();
    }

    public function testClearInterceptors()
    {
        $i = new TestInterceptor();
        $this->client->interceptors->add($i);
        $this->client->interceptors->clear();
        $this->assertEquals(0, $this->client->interceptors->count());
    }

    public function testAddInterceptor()
    {
        $i = new TestInterceptor();
        $this->client->interceptors->add($i);
        $this->assertContains($i, $this->client->interceptors->getAll());
    }

    /**
     * @depends testClearInterceptors
     * @depends testAddInterceptor
     */
    public function testCountInterceptors()
    {
        $this->client->interceptors->clear();
        $i1 = new TestInterceptor();
        $this->client->interceptors->add($i1);
        $i1 = new TestInterceptor();
        $this->client->interceptors->add($i1);

        $this->assertEquals(2, $this->client->interceptors->count());
    }
}
