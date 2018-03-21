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

namespace Grphp\Client;

/**
 * Manages a collection of http headers
 */
class HeaderCollection
{
    /** @var Header[] */
    protected $headers = [];

    /**
     * Add a value to a header
     *
     * @param string $name The name of the header
     * @param string $value A value for the header. Multiple values can be added to a header.
     * @return HeaderCollection
     */
    public function add(string $name, string $value): HeaderCollection
    {
        $name = trim(strtolower($name));
        if (!array_key_exists($name, $this->headers)) {
            $this->headers[$name] = new Header($name);
        }
        $this->headers[$name]->addValue($value);
        return $this;
    }

    /**
     * Get a specific header
     *
     * @param string $k
     * @return Header|null
     */
    public function get(string $k)
    {
        return $this->headers[trim(strtolower($k))] ?? null;
    }

    /**
     * Return all headers as a pure array format
     *
     * @return array
     */
    public function toArray(): array
    {
        $hs = [];
        foreach ($this->headers as $k => $header) {
            $hs[$k] = $header->getValues();
        }
        return $hs;
    }

    /**
     * Compress all headers into an HTTP-friendly format
     *
     * @return array
     */
    public function compress(): array
    {
        $hs = [];
        foreach ($this->headers as $k => $header) {
            $vs = $header->getValues();
            $v = trim(implode(',', $vs));
            $hs[] = trim("$k: $v");
        }
        return $hs;
    }
}
